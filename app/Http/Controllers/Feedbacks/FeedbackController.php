<?php

namespace App\Http\Controllers\Feedbacks;

use App\Models\Feedbacks\Feedback;
use App\Models\Feedbacks\FeedbackImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;

class FeedbackController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $feedbacks = Feedback::latest()
            ->select('id', 'feedback', 'company', 'logo')
            ->with('images')
            ->paginate($this->PAGINATE);

        return response([
            'feedbacks' => $feedbacks
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
            foreach($imgs as $item) {
                FeedbackImage::create([ 
                    'feedback_id' => $feedback->id,
                    'img' => $item
                ]);
            }
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
        $request->validate([
            'feedback' => 'required',
            'company' => 'nullable',
            'logo' => 'nullable|max:255',
            'images' => 'nullable|array',
        ]);

        $feedback->update([
            'feedback' => $request->feedback,
            'company' => isset($request->company) ? $request->company : null,
            'logo' => isset($request->logo) ? $request->logo : null,
        ]);

        $old_images_ids = $feedback->images()->pluck('id')->toArray();
        if(!empty($request->images)) {
            $request_old_imgs_ids = [];
            $new_imgs = []; // ["img1", "img2"]
            // dd($request->images);
            foreach($request->images as $item) {
                if($item['id'] == 0) {
                    if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $item['img'])[count(explode('/', $item['img'])) - 1])) {
                        $explode_img = explode('/', $item['img']);
                        Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/feedbacks/' . $explode_img[count($explode_img) - 1]);
                        Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/feedbacks/200/' . $explode_img[count($explode_img) - 1]);
                        Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/feedbacks/600/' . $explode_img[count($explode_img) - 1]);
                        $new_imgs[] = $explode_img[count($explode_img) - 1];
                    }
                } else {
                    $request_old_imgs_ids[] = $item['id'];
                }
            }
            FeedbackImage::where('feedback_id', $feedback->id)
                ->whereNotIn('id', $request_old_imgs_ids)
                ->delete();

            if(isset($new_imgs) && !empty($new_imgs)) {
                foreach($new_imgs as $item) {
                    FeedbackImage::create([ 
                        'feedback_id' => $feedback->id,
                        'img' => $item
                    ]);
                }
            }
        } else {
            $feedback->images()->delete();
        }

        return response([
            'feedback' => $feedback
        ]);
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
