<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Book $book): RedirectResponse
    {
        $request->validate([
            'rate'    => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $exists = Review::where('user_id', $request->user()->id)
            ->where('book_id', $book->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['rate' => 'You have already reviewed this book.']);
        }

        Review::create([
            'user_id' => $request->user()->id,
            'book_id' => $book->id,
            'rate'    => $request->rate,
            'comment' => $request->comment,
        ]);

        return redirect()->route('books.show', $book)->with('success', 'Review submitted.');
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        if ($review->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'rate'    => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review->update([
            'rate'    => $request->rate,
            'comment' => $request->comment,
        ]);

        return redirect()->route('books.show', $review->book_id)->with('success', 'Review updated.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        $user = $request->user();

        if ($review->user_id !== $user->id && $user->role !== 'staff') {
            abort(403);
        }

        $bookId = $review->book_id;
        $review->delete();

        return redirect()->route('books.show', $bookId)->with('success', 'Review deleted.');
    }
}
