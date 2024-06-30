<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOption;
use App\Http\Requests\StorePurchaseOptionRequest;
use App\Http\Requests\UpdatePurchaseOptionRequest;
use Exception;

class PurchaseOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = PurchaseOption::all();
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
    public function store(StorePurchaseOptionRequest $request)
    {
        $new = new PurchaseOption;
        $new->product_id = $request->product_id;
        $new->unit_id = $request->unit_id;
        $new->name = $request->name;
        $new->net_weight = $request->net_weight;
        $new->distributor_id = $request->distributor_id;
        $new->declared_price = $request->declared_price;
        $new->save();
        return response()->json([
            'message' => "201"
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = PurchaseOption::find($id);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOption $purchaseOption)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseOptionRequest $request, $id)
    {
        $item = PurchaseOption::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->product_id = $request->product_id;
        $item->unit_id = $request->unit_id;
        $item->name = $request->name;
        $item->net_weight = $request->net_weight;
        $item->distributor_id = $request->distributor_id;
        $item->declared_price = $request->declared_price;

        $item->save();
        return response()->json([
            'message' => ''
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = PurchaseOption::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->save();
        return response()->json($item, 200);
    }
}
