<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(private RecommendationService $recommendations) {}

    public function home(Request $request): Response
    {
        return Inertia::render('Home', [
            'recommendations' => $this->recommendations->forUser(auth()->user()),
        ]);
    }
}
