<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function forUser(?User $user, int $limit = 10): Collection
    {
        if (!$user) {
            return $this->topRated($limit);
        }

        $favouriteAuthorIds = $user->favouriteAuthors()->pluck('authors.id');

        // Genre IDs from past borrowings and reviews.
        $borrowedBookIds = $user->borrowings()->pluck('book_id');
        $reviewedBookIds = $user->reviews()->pluck('book_id');
        $interactedBookIds = $borrowedBookIds->merge($reviewedBookIds)->unique();

        $preferredGenreIds = Book::whereIn('id', $interactedBookIds)
            ->with('genres')
            ->get()
            ->flatMap(fn (Book $b) => $b->genres->pluck('id'))
            ->unique();

        // Active borrow IDs to exclude.
        $activeBorrowedIds = $user->borrowings()
            ->whereNull('return_date')
            ->pluck('book_id');

        $collected = collect();

        // Bucket 1: books by favourite authors.
        if ($favouriteAuthorIds->isNotEmpty()) {
            $bucket1 = Book::query()
                ->with(['author', 'images' => fn ($q) => $q->limit(1)])
                ->withAvg('reviews', 'rate')
                ->whereIn('author_id', $favouriteAuthorIds)
                ->whereNotIn('id', $activeBorrowedIds)
                ->orderByDesc('reviews_avg_rate')
                ->get();

            $collected = $collected->merge($bucket1);
        }

        // Bucket 2: books in preferred genres (not already in bucket 1).
        if ($preferredGenreIds->isNotEmpty() && $collected->count() < $limit) {
            $excludeIds = $activeBorrowedIds->merge($collected->pluck('id'));

            $bucket2 = Book::query()
                ->with(['author', 'images' => fn ($q) => $q->limit(1)])
                ->withAvg('reviews', 'rate')
                ->whereHas('genres', fn ($q) => $q->whereIn('genres.id', $preferredGenreIds))
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('reviews_avg_rate')
                ->get();

            $collected = $collected->merge($bucket2);
        }

        // Bucket 3: top-rated fallback for remaining slots.
        if ($collected->count() < $limit) {
            $excludeIds = $activeBorrowedIds->merge($collected->pluck('id'));

            $bucket3 = $this->topRated($limit, $excludeIds->all());

            $collected = $collected->merge($bucket3);
        }

        return $collected->unique('id')->take($limit)->values();
    }

    private function topRated(int $limit, array $excludeIds = []): Collection
    {
        return Book::query()
            ->with(['author', 'images' => fn ($q) => $q->limit(1)])
            ->withAvg('reviews', 'rate')
            ->whereNotIn('id', $excludeIds)
            ->orderByDesc('reviews_avg_rate')
            ->limit($limit)
            ->get();
    }
}
