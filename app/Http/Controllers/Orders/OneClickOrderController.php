<?php

namespace App\Http\Controllers\Orders;

use App\Models\Orders\OneClickOrder;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class OneClickOrderController extends Controller
{
    protected $PAGINATE = 16;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = OneClickOrder::latest()
            ->with('product', 'product.info', 'product.images')
            ->paginate($this->PAGINATE);

        return response([
            'orders' => $orders
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
     * @param  \App\Models\Order\OneClickOrder  $oneClickOrder
     * @return \Illuminate\Http\Response
     */
    public function show(OneClickOrder $oneClickOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order\OneClickOrder  $oneClickOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OneClickOrder $oneClickOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order\OneClickOrder  $oneClickOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(OneClickOrder $oneClickOrder)
    {
        DB::beginTransaction();
        try {
            $oneClickOrder->delete();

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