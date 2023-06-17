<?php

namespace App\Http\Controllers\Products;

use App\Models\Products\ProductBadge;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBadgeController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $badges = ProductBadge::latest()
            ->with('products', 'products.info', 'products.images')
            ->paginate($this->PAGINATE);

        return response([
            'badges' => $badges
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
            'name.ru' => 'required',
        ]);
        $data = $request->all();
        $data['for_search'] = $this->for_search($request, ['name']);

        DB::beginTransaction();
        try {
            $badge = ProductBadge::create($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'badge' => $badge
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductBadge  $productBadge
     * @return \Illuminate\Http\Response
     */
    public function show(ProductBadge $productBadge)
    {
        $badge = ProductBadge::where('id', $productBadge->id)
            ->with('products')
            ->first();

        return response([
            'badge' => $badge
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductBadge  $productBadge
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductBadge $productBadge)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ru' => 'required',
        ]);
        $data = $request->all();
        $data['for_search'] = $this->for_search($request, ['name']);

        DB::beginTransaction();
        try {
            $productBadge->update($data);
            $productBadge->products()->sync($request->products);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'productBadge' => $productBadge
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductBadge  $productBadge
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductBadge $productBadge)
    {
        DB::beginTransaction();
        try {
            $productBadge->products()->detach();
            $productBadge->delete();

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
}
