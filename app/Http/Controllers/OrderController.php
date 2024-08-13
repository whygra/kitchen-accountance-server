<?php

namespace App\Http\Controllers;

use App\Models\MenuItem\Order;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use Exception;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = Order::all();
        return response()->json($all);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        $new = new Order;
        $new->is_paid = $request->is_paid;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = Order::find($id);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, $id)
    {
        $item = Order::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        $item->is_paid = $request->is_paid;

        $item->save();
        return response()->json($item, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = Order::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        $item->delete();
        return response()->json($item, 200);
    }
}
