<?php

namespace App\Http\Controllers\Attributes;

use App\Models\Attributes\AttributeGroup;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttributeGroupController extends Controller
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
        $groups = AttributeGroup::latest()
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
            'name.ru' => 'required|max:255',
        ]);

        $attribute_group = AttributeGroup::create([
            'name' => $request->name,
            'for_search' => $request->name['ru']
        ]);

        return response($attribute_group);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attributes\AttributeGroup  $attributeGroup
     * @return \Illuminate\Http\Response
     */
    public function show(AttributeGroup $attributeGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attributes\AttributeGroup  $attributeGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AttributeGroup $attributeGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attributes\AttributeGroup  $attributeGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttributeGroup $attributeGroup)
    {
        //
    }
}
