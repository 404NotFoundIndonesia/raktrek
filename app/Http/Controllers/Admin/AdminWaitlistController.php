<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\WaitingList;
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
        $waitingList->delete();

        return back()->with('success', 'Waitlist entry removed.');
    }
}
