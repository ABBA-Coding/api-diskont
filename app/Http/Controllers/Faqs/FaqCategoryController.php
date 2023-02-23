<?php

namespace App\Http\Controllers\Faqs;

use App\Models\Faqs\FaqCategory;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class FaqCategoryController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = FaqCategory::latest()
            ->select('id', 'title')
            ->with('faqs')
            ->paginate($this->PAGINATE);

        return response([
            'categories' => $categories
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
            'title.ru' => 'required|max:255',
        ]);

        $faqs_category = FaqCategory::create([
            'title' => $request->title,
            'for_search' => $this->for_search($request, ['title'])
        ]);

        return response($faqs_category);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Faqs\FaqCategory  $faqs_category
     * @return \Illuminate\Http\Response
     */
    public function show(FaqCategory $faqs_category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Faqs\FaqCategory  $faqs_category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FaqCategory $faqs_category)
    {
        $request->validate([
            'title' => 'required|array',
            'title.ru' => 'required|max:255',
        ]);

        DB::beginTransaction();
        try {
            $faqs_category->update([
                'title' => $request->title,
                'for_search' => $this->for_search($request, ['title'])
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'faqs_category' => $faqs_category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Faqs\FaqCategory  $faqs_category
     * @return \Illuminate\Http\Response
     */
    public function destroy(FaqCategory $faqs_category)
    {
        DB::beginTransaction();
        try {
            $faqs_category->delete();

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
