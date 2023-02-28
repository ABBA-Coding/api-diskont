<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    protected $PAGINATE = 16;

    public function index()
    {
        $posts = Post::latest()
            ->paginate($this->PAGINATE);

        return response([
            'posts' => $posts
        ]);
    }
}
