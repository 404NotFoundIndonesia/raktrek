<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavouriteAuthorController extends Controller
{
    public function store(Request $request, Author $author): RedirectResponse
    {
        // syncWithoutDetaching is idempotent — no duplicate row on repeat call.
        $request->user()->favouriteAuthors()->syncWithoutDetaching([$author->id]);

        return back()->with('success', 'Author added to favourites.');
    }

    public function destroy(Request $request, Author $author): RedirectResponse
    {
        $request->user()->favouriteAuthors()->detach($author->id);

        return back()->with('success', 'Author removed from favourites.');
    }
}
