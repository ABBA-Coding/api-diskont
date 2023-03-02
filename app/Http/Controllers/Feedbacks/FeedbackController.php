<?php

namespace App\Http\Controllers\Feedbacks;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'feedback' => 'required',
            'company' => 'nullable',
            'logo' => 'nullable|max:255',
            'images' => 'nullable|array',
        ]);

        $feedback = Feedback::create([
            'feedback' => $request->feedback,
            'company' => isset($request->company) ? $request->company : null,
            'logo' => isset($request->logo) ? $request->logo : null,
        ]);

        foreach($request->images as $item) {
            if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $item)[count(explode('/', $item)) - 1])) {
                $explode_img = explode('/', $item);
                Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/feedbacks/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/feedbacks/200/' . $explode_img[count($explode_img) - 1]);
                Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/feedbacks/600/' . $explode_img[count($explode_img) - 1]);
                $imgs[] = $explode_img[count($explode_img) - 1];
            }
        }

        if(isset($imgs) && !empty($imgs)) {
            FeedbackImage::create([
                'feedback_id' => $feedback
            ]);
        }

        return response([
            'feedback' => $feedback
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function show(Feedback $feedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Feedback $feedback)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(Feedback $feedback)
    {
        //
    }
}
