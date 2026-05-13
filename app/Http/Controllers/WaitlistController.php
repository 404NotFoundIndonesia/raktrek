<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\WaitingList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WaitlistController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['book_id' => 'required|integer']);

        $book = Book::findOrFail($request->book_id);

        if ($book->availability === 1) {
            return back()->withErrors(['book_id' => 'Book is available — borrow it directly.']);
        }

        $alreadyQueued = WaitingList::where('user_id', $request->user()->id)
            ->where('book_id', $book->id)
            ->exists();

        if ($alreadyQueued) {
            return back()->withErrors(['book_id' => 'You are already on the waitlist for this book.']);
        }

        WaitingList::create([
            'user_id'     => $request->user()->id,
            'book_id'     => $book->id,
            'finish_date' => now()->addDays(7),
        ]);

        return redirect()->route('books.show', $book)->with('success', 'You have joined the waitlist.');
    }

    public function destroy(WaitingList $waitingList): RedirectResponse
    {
        $user = auth()->user();

        if ($waitingList->user_id !== $user->id && $user->role !== 'staff') {
            abort(403);
        }

        $waitingList->delete();

        return back()->with('success', 'Waitlist entry removed.');
    }

    public function myWaitlists(Request $request): Response
    {
        $entries = WaitingList::with(['book.images', 'book.author'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at')
            ->get();

        // Compute queue position for each entry.
        $entries = $entries->map(function (WaitingList $entry) {
            $position = WaitingList::where('book_id', $entry->book_id)
                ->where('created_at', '<=', $entry->created_at)
                ->count();

            $entry->setAttribute('queue_position', $position);

            return $entry;
        });

        return Inertia::render('Waitlists/Index', [
            'waitlists' => $entries,
        ]);
    }
}
