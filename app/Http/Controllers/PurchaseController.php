<?php

namespace App\Http\Controllers;

use App\Models\Distributor\Purchase;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use Exception;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = Purchase::all();
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
    public function store(StorePurchaseRequest $request)
    {
        $new = new Purchase;
        $new->date = $request->date;
        $new->is_delivered = $request->is_delivered;
        $new->is_paid = $request->is_paid;
        $new->distributor_id = $request->distributor_id;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = Purchase::find($id);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseRequest $request, $id)
    {
        $item = Purchase::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        
        $item->date = $request->date;
        $item->is_delivered = $request->is_delivered;
        $item->is_paid = $request->is_paid;
        $item->distributor_id = $request->distributor_id;

        $item->save();

        return response()->json($item, 204);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = Purchase::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        
        $item->save();

        return response()->json($item, 204);
    }
}
