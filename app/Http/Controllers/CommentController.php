<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $comments = Comment::latest()
            ->select('id', 'user_id', 'product_info_id', 'comment', 'stars', 'is_active')
            ->with('user', 'product_info')
            // ->get();
            ->paginate($this->PAGINATE);

        return response([
            'comments' => $comments
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
            'user_id' => 'required|integer',
            'product_id' => 'required|integer',
            'comment' => 'required|max:1000',
            'stars' => 'required|integer|in:1,2,3,4,5'
        ]);

        DB::beginTransaction();
        try {
            $comment = Comment::create([
                'user_id' => $request->user_id,
                'product_info_id' => $request->product_id,
                'comment' => $request->comment,
                'stars' => $request->stars,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'comment' => $comment
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comment)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'product_id' => 'required|integer',
            'comment' => 'required|max:1000',
            'stars' => 'required|integer|in:1,2,3,4,5',
            'is_active' => 'nullable|boolean'
        ]);

        DB::beginTransaction();
        try {
            $comment->update([
                'user_id' => $request->input('user_id'),
                'product_info_id' => $request->input('product_id'),
                'comment' => $request->input('comment'),
                'stars' => $request->input('stars'),
                'is_active' => $request->is_active ?? ($comment->is_active ?? null),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'comment' => $comment
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        DB::beginTransaction();
        try {
            $comment->delete();

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
}
