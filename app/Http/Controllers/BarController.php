<?php

namespace App\Http\Controllers;

use App\Models\Bar;
use App\Models\Category;
use App\Models\Promotions\Promotion;
use Illuminate\Support\Facades\Storage;
use DB;
use Illuminate\Http\Request;

class BarController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bars = Bar::latest()
            ->with('category', 'category.parent', 'promotion')
            ->orderBy('position')
            ->paginate($this->PAGINATE);

        return response([
            'bars' => $bars
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
            'bars' => 'required|array',
            'bars.*.id' => 'required|integer',
            'bars.*.name' => 'required|array',
            'bars.*.name.ru' => 'required|max:500',
            'bars.*.icon' => 'nullable|max:255',
            'bars.*.text_color' => 'required|max:255',
            'bars.*.color1' => 'required|max:255',
            'bars.*.color2' => 'required|max:255',
            'bars.*.position' => 'required|integer',
        ]);
        $data = $request->all();

        DB::beginTransaction();
        try {
            foreach ($data['bars'] as $value) {
            	if($value['id'] == 0) {
            		if($value['icon'] && Storage::disk('public')->exists('/uploads/temp/' . explode('/', $value['icon'])[count(explode('/', $value['icon'])) - 1])) {
	                    $explode_img = explode('/', $data['icon']);
	                    Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/bars/' . $explode_img[count($explode_img) - 1]);
	                    Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/bars/200/' . $explode_img[count($explode_img) - 1]);
	                    Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/bars/600/' . $explode_img[count($explode_img) - 1]);
	                    $value['icon'] = $explode_img[count($explode_img) - 1];
	                }

	                $value['for_search'] = $this->for_search($request, ['name']);
	                Bar::create($value);
            	} else {
            		if(Bar::where('id', $value['id'])->exists()) {
                    	$item = Bar::find($value['id']);
	                    if($value['icon']) {
	                        if(Storage::disk('public')->exists('/uploads/temp/' . explode('/', $value['icon'])[count(explode('/', $value['icon'])) - 1])) {
	                            $explode_img = explode('/', $value['icon']);
	                            Storage::disk('public')->move('/uploads/temp/' . $explode_img[count($explode_img) - 1], '/uploads/bars/' . $explode_img[count($explode_img) - 1]);
	                            Storage::disk('public')->move('/uploads/temp/200/' . $explode_img[count($explode_img) - 1], '/uploads/bars/200/' . $explode_img[count($explode_img) - 1]);
	                            Storage::disk('public')->move('/uploads/temp/600/' . $explode_img[count($explode_img) - 1], '/uploads/bars/600/' . $explode_img[count($explode_img) - 1]);
	                            $value['icon'] = $explode_img[count($explode_img) - 1];
	                        } else if(Storage::disk('public')->exists('/uploads/bars/' . explode('/', $value['icon'])[count(explode('/', $value['icon'])) - 1])) {
	                            $value['icon'] = $item->icon;
	                        }
	                    }

	                    $data['for_search'] = $this->for_search($request, ['name']);
	                    $item->update($value);
	                }
            	}
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'req' => $request->all()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bar  $bar
     * @return \Illuminate\Http\Response
     */
    public function show(Bar $bar)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bar  $bar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bar $bar)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bar  $bar
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bar $bar)
    {
        DB::beginTransaction();
        try {
            $bar->delete();

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

    public function for_search(Request $request, $fields)
    {
        $result = '';

        if(count($fields) == 0) return '';

        foreach($fields as $field) {
            $result .= isset($request->$field['ru']) ? ($request->$field['ru'] . ' ') : '';
        }

        return $result;
    }

    public function search_cat_promo(Request $request)
    {
        $request->validate([
            'search' => 'required'
        ]);

        $categories = Category::where('name', 'like', '%'.$request->search.'%')
            ->orWhere('for_search', 'like', '%'.$request->search.'%')
            ->limit(16)
            ->with('parent')
            ->get();

        $promotions = Promotion::where('short_name', 'like', '%'.$request->search.'%')
            ->orWhere('name', 'like', '%'.$request->search.'%')
            ->orWhere('for_search', 'like', '%'.$request->search.'%')
            ->limit(16)
            ->get();

        return response([
            'categories' => $categories,
            'promotions' => $promotions,
        ]);
    }
}
