<?php

namespace App\Http\Controllers\Characteristics;

use App\Models\Characteristics\CharacteristicGroup;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CharacteristicGroupController extends Controller
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

    public function all()
    {
        $groups = CharacteristicGroup::latest()
            ->select('name', 'id')
            ->get();

        return response([
            'groups' => $groups
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
            'name' => 'required|array',
            'name.'.$this->main_lang => 'required|max:255',
        ]);

        $characteristic_group = CharacteristicGroup::create([
            'name' => $request->name,
            'for_search' => $this->for_search($request, ['name'])
        ]);

        return response($characteristic_group);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CharacteristicGroup  $characteristicGroup
     * @return \Illuminate\Http\Response
     */
    public function show(CharacteristicGroup $characteristicGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CharacteristicGroup  $characteristicGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CharacteristicGroup $characteristicGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CharacteristicGroup  $characteristicGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(CharacteristicGroup $characteristicGroup)
    {
        //
    }
}
