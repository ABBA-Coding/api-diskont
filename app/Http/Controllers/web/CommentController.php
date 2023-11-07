<?php

namespace App\Http\Controllers\web;

use App\Models\Comment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'comment' => 'required|max:1000',
            'stars' => 'required|integer|in:1,2,3,4,5'
        ]);

        /*
         *
         */
        if(!auth('sanctum')->user()) {
            return response([
                'message' => 'Nujno loginitsya chtobi ostavit otziv'
            ], 401);
        }

        DB::beginTransaction();
        try {
            $comment = Comment::create([
                'user_id' => auth('sanctum')->id(),
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
            'message' => '',
            'comment' => $comment
        ]);
    }
}
