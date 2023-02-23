<?php

namespace App\Http\Controllers\Faqs;

use App\Models\Faqs\Faq;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $faqs = Faq::latest()
            ->select('id', 'question', 'answer', 'category_id')
            ->with('category')
            ->paginate($this->PAGINATE);

        return response([
            'faqs' => $faqs
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
            'question' => 'required|array',
            'question.ru' => 'required',
            'answer' => 'required|array',
            'answer.ru' => 'required',
            'category_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $faq = Faq::create([
                'question' => $request->question,
                'answer' => $request->answer,
                'category_id' => $request->category_id,
                'for_search' => $this->for_search($request, ['question', 'answer'])
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'faq' => $faq
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Faqs\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function show(Faq $faq)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Faqs\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => 'required|array',
            'question.ru' => 'required',
            'answer' => 'required|array',
            'answer.ru' => 'required',
            'category_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $faq->update([
                'question' => $request->question,
                'answer' => $request->answer,
                'category_id' => $request->category_id,
                'for_search' => $this->for_search($request, ['question', 'answer'])
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'faq' => $faq
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Faqs\Faq  $faq
     * @return \Illuminate\Http\Response
     */
    public function destroy(Faq $faq)
    {
        DB::beginTransaction();
        try {
            $faq->delete();

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
