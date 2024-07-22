<?php

namespace App\Http\Controllers;

use App\Models\DishIngredient;
use App\Http\Requests\StoreDishIngredientRequest;
use App\Http\Requests\UpdateDishIngredientRequest;
use Exception;

class DishIngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = DishIngredient::all();
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
    public function store(StoreDishIngredientRequest $request)
    {
        $new = new DishIngredient;
        $new->dish_id = $request->dish_id;
        $new->ingredient_id = $request->ingredient_id;
        $new->ingredient_weight = $request->ingredient_weight;
        $new->waste_percentage = $request->waste_percentage;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = DishIngredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DishIngredient $dishIngredient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishIngredientRequest $request, $id)
    {
        $item = DishIngredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
        
        $item->dish_id = $request->dish_id;
        $item->ingredient_id = $request->ingredient_id;
        $item->ingredient_weight = $request->ingredient_weight;
        $item->waste_percentage = $request->waste_percentage;
        $item->save();
        return response()->json($item, 204);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = DishIngredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
        
        $item->delete();
        return response()->json($item, 204);    
    }
}
