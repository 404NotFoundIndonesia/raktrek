<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminBorrowingController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Borrowing::with(['user', 'book'])
            ->orderByDesc('created_at');

        $filter = $request->get('filter');

        match ($filter) {
            'overdue'  => $query->whereNull('return_date')->where('due_date', '<', now()),
            'active'   => $query->whereNull('return_date')->where('due_date', '>=', now()),
            'returned' => $query->whereNotNull('return_date'),
            default    => null,
        };

        return Inertia::render('Admin/Borrowings/Index', [
            'borrowings' => $query->paginate(20)->withQueryString(),
            'filter'     => $filter,
        ]);
    }

    public function processReturn(Borrowing $borrowing): RedirectResponse
    {
        if (!is_null($borrowing->return_date)) {
            return back()->withErrors(['borrowing' => 'This borrowing has already been returned.']);
        }

        BorrowingController::doReturn($borrowing);

        return back()->with('success', 'Return processed successfully.');
    }
}
