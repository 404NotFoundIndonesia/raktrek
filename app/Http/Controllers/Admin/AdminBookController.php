<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use App\Notifications\NewBookByFavouriteAuthorNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminBookController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'author_id'        => ['nullable', 'exists:authors,id'],
            'synopsis'         => ['nullable', 'string'],
            'publisher'        => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer'],
            'language'         => ['nullable', 'string', 'max:100'],
            'page_number'      => ['nullable', 'integer', 'min:1'],
            'availability'     => ['nullable', 'integer', 'in:0,1,2,3'],
        ]);

        $book = Book::create([
            'title'            => $data['title'],
            'author_id'        => $data['author_id'] ?? null,
            'synopsis'         => $data['synopsis'] ?? '',
            'publisher'        => $data['publisher'] ?? '',
            'publication_year' => $data['publication_year'] ?? now()->year,
            'language'         => $data['language'] ?? 'English',
            'page_number'      => $data['page_number'] ?? 0,
            'availability'     => $data['availability'] ?? 1,
        ]);

        if ($book->author_id) {
            $fans = User::whereHas(
                'favouriteAuthors',
                fn ($q) => $q->where('authors.id', $book->author_id)
            )->get();

            $book->load('author');

            foreach ($fans as $fan) {
                $fan->notify(new NewBookByFavouriteAuthorNotification($book));
            }
        }

        return redirect()->back()->with('success', 'Book created successfully.');
    }
}
