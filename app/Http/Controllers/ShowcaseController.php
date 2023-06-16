<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Showcase;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Showcase  $showcase
     * @return \Illuminate\Http\Response
     */
    public function show(Showcase $showcase)
    {
        $showcase = Showcase::with('products', 'products.info', 'products.images')
            ->where('id', $showcase->id)
            ->first();

        return response([
            'showcase' => $showcase
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Showcase  $showcase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Showcase $showcase)
    {
        $request->validate([
            'name' => 'required',
            'products' => 'required|array'
        ]);
        $data = $request->all();

        $showcase = Showcase::find($showcase->id);
        if(!$showcase) return response([
            'message' => 'Showcase not found'
        ], 404);

        DB::beginTransaction();
        try {
            $showcase->update([
                'name' => $data['name'],
                'for_search' => isset($data['name']['ru']) ? $data['name']['ru'] : ''
            ]);

            $sync_products = [];
            foreach ($data['products'] as $item) {
                $sync_products[$item['id']] = [
                    'position' => $item['position']
                ];
            }
            $showcase->products()->sync($sync_products);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        $showcase = Showcase::where('id', $showcase->id)
            ->with('products')
            ->first();

        return response([
            'showcase' => $showcase
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Showcase  $showcase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Showcase $showcase)
    {
        //
    }

    public function all()
    {
        return response([
            'showcases' => Showcase::with('products', 'products.info', 'products.images')
                ->get()
        ]);
    }
}
