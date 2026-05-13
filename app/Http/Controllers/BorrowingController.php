<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Notifications\BookAvailableNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BorrowingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['book_id' => 'required|integer']);

        $book = Book::findOrFail($request->book_id);

        if ($book->availability !== 1) {
            return back()->withErrors(['book_id' => 'This book is not available for borrowing.']);
        }

        $alreadyBorrowed = Borrowing::where('user_id', $request->user()->id)
            ->where('book_id', $book->id)
            ->whereNull('return_date')
            ->exists();

        if ($alreadyBorrowed) {
            return back()->withErrors(['book_id' => 'You already have this book checked out.']);
        }

        Borrowing::create([
            'user_id'  => $request->user()->id,
            'book_id'  => $book->id,
            'due_date' => now()->addDays(14),
        ]);

        $book->update(['availability' => 0]);

        return redirect()->route('books.show', $book)->with('success', 'Book borrowed successfully.');
    }

    public function processReturn(Borrowing $borrowing): RedirectResponse
    {
        if ($borrowing->user_id !== auth()->id()) {
            abort(403);
        }

        if (!is_null($borrowing->return_date)) {
            return back()->withErrors(['borrowing' => 'This borrowing has already been returned.']);
        }

        $this->doReturn($borrowing);

        return back()->with('success', 'Book returned successfully.');
    }

    public function renew(Borrowing $borrowing): RedirectResponse
    {
        if ($borrowing->user_id !== auth()->id()) {
            abort(403);
        }

        if ($borrowing->book->reservations()->exists()) {
            return back()->withErrors(['borrowing' => 'Cannot renew — other members are waiting for this book.']);
        }

        $borrowing->update(['due_date' => $borrowing->due_date->addDays(14)]);

        return back()->with('success', 'Borrowing renewed. New due date: ' . $borrowing->due_date->toDateString());
    }

    public function myBorrowings(Request $request): Response
    {
        $borrowings = Borrowing::with(['book.images', 'book.author'])
            ->where('user_id', $request->user()->id)
            ->orderByRaw('return_date IS NOT NULL, due_date ASC')
            ->paginate(15);

        return Inertia::render('Borrowings/Index', [
            'borrowings' => $borrowings,
        ]);
    }

    public static function doReturn(Borrowing $borrowing): void
    {
        $borrowing->update(['return_date' => now()]);
        $borrowing->book->update(['availability' => 1]);

        $firstInQueue = $borrowing->book->reservations()->orderBy('created_at')->first();
        if ($firstInQueue) {
            $firstInQueue->user->notify(new BookAvailableNotification($borrowing->book));
        }
    }
}
