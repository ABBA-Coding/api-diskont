<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use DB;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::latest()
            ->select('id', 'title', 'desc', 'img')
            // ->with('parent', 'attribute_groups', 'attribute_groups.attributes', 'characteristic_groups', 'characteristic_groups.characteristics')
            ->paginate($this->PAGINATE);

        return response([
            'posts' => $posts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|array',
            'title.ru' => 'required|max:500',
            'img' => 'nullable|max:255',
            'desc' => 'required|array',
            'desc.ru' => 'required',
        ]);

        if($request->img && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->img)[count(explode('/', $request->img)) - 1])) {
            $explode_img = explode('/', $request->img);
            Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/posts/' . $explode_img[count($explode_img) - 1]);
            Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/posts/200/' . $explode_img[count($explode_img) - 1]);
            Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/posts/600/' . $explode_img[count($explode_img) - 1]);
            $img = $explode_img[count($explode_img) - 1];
        }

        DB::beginTransaction();
        try {
            $post = Post::create([
                'title' => $request->title,
                'desc' => $request->desc,
                'img' => $request->img ? $img : null,
                'for_search' => $this->for_search($request, ['title', 'desc'])
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'post' => $post
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|array',
            'title.ru' => 'required|max:500',
            'img' => 'nullable|max:255',
            'desc' => 'required|array',
            'desc.ru' => 'required',
        ]);

        if($request->img) {
            if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $request->img)[count(explode('/', $request->img)) - 1])) {
                $explode_img = explode('/', $request->img);
                Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/posts/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/posts/200/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/posts/600/' . $explode_img[count($explode_img) - 1]);
                $img = $explode_img[count($explode_img) - 1];
            } else if(Storage::disk('public')->exists('/uploads/posts/' . explode('/', $request->img)[count(explode('/', $request->img)) - 1])) {
                $img = $post->img;
            }
        }

        DB::beginTransaction();
        try {
            $post->update([
                'title' => $request->title,
                'desc' => $request->desc,
                'img' => isset($img) ? $img : $request->img,
                'for_search' => $this->for_search($request, ['title', 'desc'])
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'post' => $post
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        DB::beginTransaction();
        try {
            $post->delete();

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();

            return reponse([
                'message' => $e->getMessage()
            ], 500);
        }
        
        return response([
            'message' => __('messages.successfully_deleted')
        ]);
    }

    private function for_search(Request $request, $fields)
    {
        $result = '';

        if(count($fields) == 0) return '';

        foreach($fields as $field) {
            $result .= isset($request->$field['ru']) ? ($request->$field['ru'] . ' ') : '';
        }

        return $result;
    }
}
