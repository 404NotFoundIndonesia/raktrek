<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function home(Request $request): Response {
        return Inertia::render('Home', []);
    }

    public function explore(Request $request): Response {
        return Inertia::render('Explore', []);
    }
}
