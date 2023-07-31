<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Discount;
use App\Models\Products\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $discounts = Discount::latest()
            ->with('products')
            ->paginate($this->PAGINATE);

        // foreach ($discounts as $discount) {
        //     $this->append_data($discount);
        // }

        return response([
            'discounts' => $discounts,
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
            'title.ru' => 'required',
            'products' => 'required|array',
            'products.*.percent' => 'nullable|integer',
            'products.*.amount' => 'nullable|integer',
            'products.*.id' => 'required|integer',
            // 'percent' => 'nullable|integer',
            // 'amount' => 'nullable|integer',
            // 'ids' => 'array|required',
            'type' => 'required|in:product,brand',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'status' => 'required|boolean'
        ]);
        foreach ($request->products as $prouct) {
            if(!($prouct['percent'] || $prouct['amount'])) return response([
                'message' => 'Odnovremenno amount i percent ne mojet bit pustim'
            ], 422);   
        }

        $data = $request->all();
        $data['for_search'] = $data['title']['ru'].(isset($data['desc']['ru']) ? ' '.$data['desc']['ru'] : '');

        DB::beginTransaction();
        try {
            $discount = Discount::create($data);
            foreach ($data['products'] as $product) {
                $discount->products()->attach($product['id'], ['percent' => $product['percent'], 'amount' => $product['amount']]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'discount' => $discount
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function show(Discount $discount)
    {
    	$discount = Discount::where('id', $discount->id)
    		->with('products')
			->first();

        return response([
            'discount' => $discount,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'title' => 'required|array',
            'title.ru' => 'required',
            'products' => 'required|array',
            'products.*.percent' => 'nullable|integer',
            'products.*.amount' => 'nullable|integer',
            'products.*.id' => 'required|integer',
            // 'percent' => 'nullable|integer',
            // 'amount' => 'nullable|integer',
            // 'ids' => 'array|required',
            'type' => 'required|in:product,brand',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'status' => 'required|boolean'
        ]);
        foreach ($request->products as $prouct) {
            if(!($prouct['percent'] || $prouct['amount'])) return response([
                'message' => 'Odnovremenno amount i percent ne mojet bit pustim'
            ], 422);   
        }

        $data = $request->all();
        $data['for_search'] = $data['title']['ru'].(isset($data['desc']['ru']) ? ' '.$data['desc']['ru'] : '');

        DB::beginTransaction();
        try {
            $discount->update($data);
            $discount->products()->detach();
            foreach ($data['products'] as $product) {
                $discount->products()->attach($product['id'], ['percent' => $product['percent'], 'amount' => $product['amount']]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage()
            ], 500);
        }

        return response([
            'discount' => $discount
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discount $discount)
    {
        //
    }

    /**
     * @param Discount $discount
     * @return void
     */
    // public function append_data(Discount $discount): void
    // {
    //     if ($discount->type == 'product') {
    //         $discount->products = Product::where('id', $discount->pivot->product_id)->get();
    //         $discount->brands = null;
    //     } else if ($discount->type == 'brand') {
    //         $discount->brands = Brand::where('id', $discount->id)->get();
    //         $discount->products = null;
    //     }
    // }
}
