<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('categories.index', [
            'user' => $user,
        ]);
    }
}
