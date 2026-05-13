<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthorController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Author::withCount('books');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        return Inertia::render('Admin/Authors/Index', [
            'authors' => $query->orderBy('name')->paginate(20)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Authors/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'photo' => ['nullable', 'file', 'image', 'max:2048'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('authors', 'public');
        }

        Author::create([
            'name'  => $data['name'],
            'about' => $data['about'] ?? '',
            'photo' => $photoPath,
        ]);

        return redirect()->route('admin.authors.index')->with('success', 'Author created.');
    }

    public function edit(Author $author): Response
    {
        return Inertia::render('Admin/Authors/Edit', ['author' => $author]);
    }

    public function update(Request $request, Author $author): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'photo' => ['nullable', 'file', 'image', 'max:2048'],
        ]);

        $photoPath = $author->photo;
        if ($request->hasFile('photo')) {
            if ($author->photo) {
                Storage::disk('public')->delete($author->photo);
            }
            $photoPath = $request->file('photo')->store('authors', 'public');
        }

        $author->update([
            'name'  => $data['name'],
            'about' => $data['about'] ?? $author->about ?? '',
            'photo' => $photoPath,
        ]);

        return redirect()->route('admin.authors.index')->with('success', 'Author updated.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        if ($author->books()->count() > 0) {
            return back()->withErrors(['author' => 'Cannot delete author with existing books.']);
        }

        if ($author->photo) {
            Storage::disk('public')->delete($author->photo);
        }

        $author->delete();

        return back()->with('success', 'Author deleted.');
    }
}
