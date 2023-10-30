<?php

namespace App\Http\Controllers\Characteristics;

use App\Models\Characteristics\CharacteristicOption;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CharacteristicOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $options = CharacteristicOption::with('characteristic', 'products')
            ->latest();
        if(isset($request->search) && $request->search != '') {
            $options = $options->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->input('search').'%')
                    ->orWhere('for_search', 'like', '%'.$request->input('search').'%');
            })
                ->orWhereHas('products', function ($q) use ($request) {
                    $q->where(function ($qi) use ($request) {
                        $qi->where('name', 'like', '%'.$request->input('search').'%')
                            ->orWhere('for_search', 'like', '%'.$request->input('search').'%');
                    });
                });
        }
        $options = $options->paginate(24);

        return response([
            'options' => $options,
            'search' => isset($request->search) ? $request->search : null
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CharacteristicOption  $characteristicOption
     * @return \Illuminate\Http\Response
     */
    public function show(CharacteristicOption $characteristicOption)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CharacteristicOption  $characteristicOption
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CharacteristicOption $characteristicOption)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CharacteristicOption  $characteristicOption
     * @return \Illuminate\Http\Response
     */
    public function destroy(CharacteristicOption $characteristicOption)
    {
        //
    }

    public function store_more(Request $request)
    {
        $request->validate([
            'options' => 'required|array',
            'options.*.id' => 'integer|required',
            'options.*.name.'.$this->main_lang => 'required',
        ]);

        foreach($request->options as $option) {
            CharacteristicOption::find($option['id'])->update([
                'name' => $option['name'],
                'for_search' => $option['name'][$this->main_lang]
            ]);
        }

        return response([
            'message' => 'Successfully saved'
        ]);
    }
}
