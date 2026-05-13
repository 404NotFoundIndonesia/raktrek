<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Book::query()
            ->with(['author', 'genres', 'images' => fn ($q) => $q->limit(1)])
            ->withAvg('reviews', 'rate');

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($genreId = $request->get('genre')) {
            $query->whereHas('genres', fn ($q) => $q->where('genres.id', $genreId));
        }

        if ($year = $request->get('year')) {
            $query->where('publication_year', $year);
        }

        if ($language = $request->get('language')) {
            $query->where('language', $language);
        }

        if ($request->filled('availability')) {
            $query->where('availability', (int) $request->get('availability'));
        }

        $sort = $request->get('sort', 'title');
        match ($sort) {
            'rating' => $query->orderByDesc('reviews_avg_rate'),
            'year'   => $query->orderByDesc('publication_year'),
            default  => $query->orderBy('title'),
        };

        $books = $query->paginate(15)->withQueryString();

        return Inertia::render('Books/Index', [
            'books'  => $books,
            'genres' => Genre::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'genre', 'year', 'language', 'availability', 'sort']),
        ]);
    }

    public function show(Book $book): Response
    {
        $book->load([
            'author',
            'genres',
            'images',
            'reviews.user',
        ]);

        $averageRating = $book->averageRating();

        $userBorrowing        = null;
        $userWaitlistPosition = null;
        $userReview           = null;

        if ($user = auth()->user()) {
            $userBorrowing = $book->histories()
                ->where('user_id', $user->id)
                ->whereNull('return_date')
                ->first();

            if (!$userBorrowing) {
                $position = $book->reservations()
                    ->orderBy('created_at')
                    ->pluck('user_id')
                    ->search($user->id);

                $userWaitlistPosition = $position !== false ? $position + 1 : null;
            }

            $userReview = $book->reviews->firstWhere('user_id', $user->id);

            $isFavouritedAuthor = $book->author_id
                ? $user->favouriteAuthors()->where('authors.id', $book->author_id)->exists()
                : false;
        }

        return Inertia::render('Books/Show', [
            'book'                 => $book,
            'averageRating'        => $averageRating,
            'availabilityLabel'    => $book->availabilityLabel(),
            'userBorrowing'        => $userBorrowing,
            'userWaitlistPosition' => $userWaitlistPosition,
            'userReview'           => $userReview,
            'isFavouritedAuthor'   => $isFavouritedAuthor ?? false,
        ]);
    }
}
