<?php

namespace App\Http\Controllers;

use App\Models\MenuItemOrder;
use App\Http\Requests\StoreMenuItemOrderRequest;
use App\Http\Requests\UpdateMenuItemOrderRequest;
use App\Models\MenuItem;
use Exception;

class MenuItemOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = MenuItemOrder::all();
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
    public function store(StoreMenuItemOrderRequest $request)
    {
        $new = new MenuItemOrder;
        $new->order_id = $request->order_id;
        $new->menu_item_id = $request->menu_item_id;
        $new->amount = $request->amount;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = MenuItemOrder::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MenuItemOrder $menuItemOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMenuItemOrderRequest $request, $id)
    {
        $item = MenuItemOrder::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->order_id = $request->order_id;
        $item->menu_item_id = $request->menu_item_id;
        $item->amount = $request->amount;
        $item->save();
        return response()->json($item, 204);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = MenuItemOrder::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->delete();
        return response()->json($item, 204);
    }
}
