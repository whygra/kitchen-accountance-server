<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ingredient\DeleteIngredientRequest;
use App\Http\Requests\Ingredient\GetIngredientRequest;
use App\Http\Requests\IngredientCategory\StoreIngredientCategoryWithIngredientsRequest;
use App\Http\Requests\IngredientCategory\UpdateIngredientCategoryWithIngredientsRequest;
use App\Models\Ingredient\IngredientCategory;
use App\Models\Ingredient\IngredientIngredient;

class IngredientCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetIngredientRequest $request)
    {
        $all = IngredientCategory::all();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIngredientCategoryWithIngredientsRequest $request)
    {
        $new = new IngredientCategory;
        $new->name = $request->name;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetIngredientRequest $request, $id)
    {
        $item = IngredientCategory::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIngredientCategoryWithIngredientsRequest $request, $id)
    {
        $item = IngredientCategory::find($id);
        $item->name = $request->name;
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        $item->name = $request->name;
        $item->save();
            return response()->json($item, 200);
    }

    /**
     * Display the specified resource.
     */

    public function index_loaded(GetIngredientRequest $request)
    {
        $all = IngredientCategory::with('ingredients')->all();
        return response()->json($all);
    }
     

    public function show_loaded(GetIngredientRequest $request, $id)
    {
        $item = IngredientCategory::with('ingredients')->find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }
    
    // удаление
    public function destroy(DeleteIngredientRequest $request, $id) 
    {
        $item = IngredientCategory::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию блюда с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }
}
