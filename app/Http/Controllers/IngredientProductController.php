<?php

namespace App\Http\Controllers;

use App\Models\IngredientProduct;
use App\Http\Requests\StoreIngredientProductRequest;
use App\Http\Requests\UpdateIngredientProductRequest;
use Exception;


class IngredientProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = IngredientProduct::all();
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
    public function store(StoreIngredientProductRequest $request)
    {
        $new = new IngredientProduct;
        $new->ingredient_id = $request->ingredient_id;
        $new->product_id = $request->product_id;
        $new->waste_percentage = $request->waste_percentage;
        $new->raw_content_percentage = $request->raw_content_percentage;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = IngredientProduct::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IngredientProduct $ingredientProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIngredientProductRequest $request, $id)
    {
        $item = IngredientProduct::find($id);
        if (empty($item))
            return response()->json([
                'message' => '404'
            ], 404);

        $item->ingredient_id = $request->ingredient_id;
        $item->product_id = $request->product_id;
        $item->waste_percentage = $request->waste_percentage;
        $item->raw_content_percentage = $request->raw_content_percentage;
        $item->save();
        
        return response()->json($item, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = IngredientProduct::find($id);
        if (empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        $item->delete();
        return response()->json($item, 202);
    }
}
