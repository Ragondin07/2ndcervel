<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function placeholder(string $title, string $description): View
    {
        return view('placeholder', [
            'title' => $title,
            'description' => $description,
        ]);
    }
}
