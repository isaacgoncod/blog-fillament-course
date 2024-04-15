<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        return view('post.index', [
            'posts' => Post::take(3)->get(),
        ]);
    }
}
