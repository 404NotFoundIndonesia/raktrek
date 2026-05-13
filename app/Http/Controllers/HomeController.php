<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function home(Request $request): Response
    {
        $user = auth()->user();

        if ($user) {
            $favouriteAuthorIds = $user->favouriteAuthors()->pluck('authors.id');

            $recommendations = Book::query()
                ->with(['author', 'images' => fn ($q) => $q->limit(1)])
                ->withAvg('reviews', 'rate')
                ->when($favouriteAuthorIds->isNotEmpty(), function ($query) use ($favouriteAuthorIds) {
                    $query->whereIn('author_id', $favouriteAuthorIds);
                })
                ->when($favouriteAuthorIds->isEmpty(), function ($query) {
                    $query->orderByDesc('reviews_avg_rate');
                })
                ->limit(10)
                ->get();
        } else {
            $recommendations = Book::query()
                ->with(['author', 'images' => fn ($q) => $q->limit(1)])
                ->withAvg('reviews', 'rate')
                ->orderByDesc('reviews_avg_rate')
                ->limit(10)
                ->get();
        }

        return Inertia::render('Home', [
            'recommendations' => $recommendations,
        ]);
    }
}
