<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminGenreController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Genres/Index', [
            'genres' => Genre::withCount('books')->orderBy('name')->paginate(30),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Genres/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('genres', 'name')],
            'description' => ['nullable', 'string'],
        ]);

        Genre::create($request->only(['name', 'description']));

        return redirect()->route('admin.genres.index')->with('success', 'Genre created.');
    }

    public function edit(Genre $genre): Response
    {
        return Inertia::render('Admin/Genres/Edit', ['genre' => $genre]);
    }

    public function update(Request $request, Genre $genre): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('genres', 'name')->ignore($genre->id)],
            'description' => ['nullable', 'string'],
        ]);

        $genre->update($request->only(['name', 'description']));

        return redirect()->route('admin.genres.index')->with('success', 'Genre updated.');
    }

    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->count() > 0) {
            return back()->withErrors(['genre' => 'Cannot delete genre that is assigned to books.']);
        }

        $genre->delete();

        return back()->with('success', 'Genre deleted.');
    }
}
