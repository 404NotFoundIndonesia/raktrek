<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\WaitingList;
use App\Notifications\BookAvailableNotification;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminWaitlistController extends Controller
{
    public function index(Book $book): Response
    {
        $queue = WaitingList::with('user')
            ->where('book_id', $book->id)
            ->orderBy('created_at')
            ->get()
            ->map(function (WaitingList $entry, int $index) {
                $entry->setAttribute('queue_position', $index + 1);
                return $entry;
            });

        return Inertia::render('Admin/Waitlists/Index', [
            'book'  => $book,
            'queue' => $queue,
        ]);
    }

    public function destroy(WaitingList $waitingList): RedirectResponse
    {
        $bookId   = $waitingList->book_id;
        $position = WaitingList::where('book_id', $bookId)
            ->where('created_at', '<=', $waitingList->created_at)
            ->count();

        $waitingList->delete();

        // Notify the new first-in-queue if the removed entry was at position 1.
        if ($position === 1) {
            $next = WaitingList::with('user')
                ->where('book_id', $bookId)
                ->orderBy('created_at')
                ->first();

            if ($next && $next->book) {
                $next->user->notify(new BookAvailableNotification($next->book));
            }
        }

        return back()->with('success', 'Waitlist entry removed.');
    }
}
