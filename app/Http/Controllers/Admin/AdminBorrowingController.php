<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use Illuminate\Http\RedirectResponse;

class AdminBorrowingController extends Controller
{
    public function processReturn(Borrowing $borrowing): RedirectResponse
    {
        if (!is_null($borrowing->return_date)) {
            return back()->withErrors(['borrowing' => 'This borrowing has already been returned.']);
        }

        BorrowingController::doReturn($borrowing);

        return back()->with('success', 'Return processed successfully.');
    }
}
