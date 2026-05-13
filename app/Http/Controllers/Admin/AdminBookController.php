<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookImage;
use App\Models\Genre;
use App\Models\User;
use App\Notifications\NewBookByFavouriteAuthorNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminBookController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Book::withTrashed()
            ->with(['author', 'genres', 'images' => fn ($q) => $q->limit(1)])
            ->withAvg('reviews', 'rate');

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $books = $query->orderBy('title')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Books/Index', [
            'books'   => $books,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Books/Create', [
            'authors' => Author::orderBy('name')->get(['id', 'name']),
            'genres'  => Genre::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'author_id'        => ['nullable', 'exists:authors,id'],
            'synopsis'         => ['nullable', 'string'],
            'publisher'        => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:' . (date('Y') + 1)],
            'language'         => ['nullable', 'string', 'max:100'],
            'page_number'      => ['nullable', 'integer', 'min:1'],
            'availability'     => ['nullable', 'integer', 'in:0,1,2,3'],
            'genres'           => ['nullable', 'array'],
            'genres.*'         => ['exists:genres,id'],
            'images'           => ['nullable', 'array'],
            'images.*'         => ['file', 'image', 'max:4096'],
            'image_descriptions' => ['nullable', 'array'],
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

        if (!empty($data['genres'])) {
            $book->genres()->sync($data['genres']);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('books', 'public');
                BookImage::create([
                    'book_id'     => $book->id,
                    'path'        => $path,
                    'description' => $data['image_descriptions'][$index] ?? null,
                ]);
            }
        }

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

        return redirect()->route('admin.books.index')->with('success', 'Book created successfully.');
    }

    public function edit(Book $book): Response
    {
        $book->load(['genres', 'images', 'author']);

        return Inertia::render('Admin/Books/Edit', [
            'book'    => $book,
            'authors' => Author::orderBy('name')->get(['id', 'name']),
            'genres'  => Genre::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'author_id'        => ['nullable', 'exists:authors,id'],
            'synopsis'         => ['nullable', 'string'],
            'publisher'        => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:' . (date('Y') + 1)],
            'language'         => ['nullable', 'string', 'max:100'],
            'page_number'      => ['nullable', 'integer', 'min:1'],
            'availability'     => ['nullable', 'integer', 'in:0,1,2,3'],
            'genres'           => ['nullable', 'array'],
            'genres.*'         => ['exists:genres,id'],
            'images'           => ['nullable', 'array'],
            'images.*'         => ['file', 'image', 'max:4096'],
            'image_descriptions' => ['nullable', 'array'],
        ]);

        $book->update([
            'title'            => $data['title'],
            'author_id'        => $data['author_id'] ?? null,
            'synopsis'         => $data['synopsis'] ?? $book->synopsis,
            'publisher'        => $data['publisher'] ?? $book->publisher,
            'publication_year' => $data['publication_year'] ?? $book->publication_year,
            'language'         => $data['language'] ?? $book->language,
            'page_number'      => $data['page_number'] ?? $book->page_number,
            'availability'     => $data['availability'] ?? $book->availability,
        ]);

        if (isset($data['genres'])) {
            $book->genres()->sync($data['genres']);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('books', 'public');
                BookImage::create([
                    'book_id'     => $book->id,
                    'path'        => $path,
                    'description' => $data['image_descriptions'][$index] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.books.index')->with('success', 'Book updated successfully.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return back()->with('success', 'Book deleted.');
    }
}
