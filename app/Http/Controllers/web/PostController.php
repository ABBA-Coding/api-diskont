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
            ->select('id', 'title', 'desc', 'img', 'slug', 'created_at')
            ->paginate($this->PAGINATE);

        return response([
            'posts' => $posts
        ]);
    }

    public function show($slug)
    {
        $post = Post::where('slug', $slug)
            ->select('id', 'title', 'desc', 'img', 'slug', 'created_at')
            ->first();
        $other_posts = Post::where('slug', '!=', $slug)
            ->latest()
            ->select('id', 'title', 'desc', 'img', 'slug', 'created_at')
            ->limit(4)
            ->get();

        return response([
            'post' => $post,
            'other_posts' => $other_posts,
        ]);
    }
}
