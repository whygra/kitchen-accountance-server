<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;
use App\Http\Requests\StorePurchaseItemRequest;
use App\Http\Requests\UpdatePurchaseItemRequest;
use Exception;

class PurchaseItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = PurchaseItem::all();
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
    public function store(StorePurchaseItemRequest $request)
    {
        $new = new PurchaseItem;
        $new->purchase_option_id = $request->purchase_option_id;
        $new->purchase_id = $request->purchase_id;
        $new->amount = $request->amount;
        $new->price = $request->price;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = PurchaseItem::find($id);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseItem $purchaseItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseItemRequest $request, $id)
    {
        $item = PurchaseItem::find($id);
        if(empty($item))  
            return response()->json([
                'message' => ''
            ], 404);

        $item->purchase_option_id = $request->purchase_option_id;
        $item->purchase_id = $request->purchase_id;
        $item->amount = $request->amount;
        $item->price = $request->price;

        $item->save();
        return response()->json($item, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = PurchaseItem::find($id);
        if(empty($item))  
            return response()->json([
                'message' => ''
            ], 404);

        $item->delete();
        return response()->json($item, 200);
    }
}
