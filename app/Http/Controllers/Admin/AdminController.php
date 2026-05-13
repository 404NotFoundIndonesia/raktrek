<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_books'       => Book::count(),
                'active_borrowings' => Borrowing::whereNull('return_date')->count(),
                'overdue_count'     => Borrowing::whereNull('return_date')->where('due_date', '<', now())->count(),
                'total_members'     => User::where('role', 'member')->count(),
            ],
        ]);
    }
}
