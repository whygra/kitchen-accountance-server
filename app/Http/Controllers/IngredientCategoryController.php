<?php

namespace App\Http\Controllers;

use App\Http\Requests\IngredientCategory\StoreIngredientCategoryRequest;
use App\Http\Requests\IngredientCategory\UpdateIngredientCategoryRequest;
use App\Models\Ingredient\IngredientCategory;
use App\Models\Ingredient\IngredientIngredient;

class IngredientCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = IngredientCategory::all();
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
    public function store(StoreIngredientCategoryRequest $request)
    {
        $new = new IngredientCategory;
        $new->name = $request->name;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
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
    public function update(UpdateIngredientCategoryRequest $request, $id)
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

     public function index_loaded()
     {
         $all = IngredientCategory::with('ingredients')->all();
         return response()->json($all);
     }
     

    public function show_loaded($id)
    {
        $item = IngredientCategory::with('ingredients')->find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }
    
    // удаление
    public function destroy($id) 
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
