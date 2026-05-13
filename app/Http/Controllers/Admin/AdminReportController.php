<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminReportController extends Controller
{
    public function index(): Response
    {
        $since = now()->subDays(30);

        $mostBorrowed = Book::withCount([
                'histories as borrow_count_30d' => fn ($q) => $q->where('created_at', '>=', $since),
            ])
            ->with(['author', 'images' => fn ($q) => $q->limit(1)])
            ->orderByDesc('borrow_count_30d')
            ->limit(10)
            ->get();

        $overdueCount = Borrowing::whereNull('return_date')
            ->where('due_date', '<', now())
            ->count();

        $overdueBorrowings = Borrowing::with(['user', 'book'])
            ->whereNull('return_date')
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->limit(20)
            ->get();

        $activeMembers = User::whereHas(
                'borrowings',
                fn ($q) => $q->where('created_at', '>=', $since)
            )
            ->where('role', 'member')
            ->count();

        return Inertia::render('Admin/Reports/Index', [
            'mostBorrowed'      => $mostBorrowed,
            'overdueCount'      => $overdueCount,
            'overdueBorrowings' => $overdueBorrowings,
            'activeMembers'     => $activeMembers,
        ]);
    }
}
