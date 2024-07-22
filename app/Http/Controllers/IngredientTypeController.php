<?php

namespace App\Http\Controllers;

use App\Models\IngredientType;
use App\Http\Requests\StoreIngredientTypeRequest;
use App\Http\Requests\UpdateIngredientTypeRequest;
use Exception;

class IngredientTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = IngredientType::all();
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
    public function store(StoreIngredientTypeRequest $request)
    {
        $new = new IngredientType;
        $new->name = $request->name;
        $new->save();
        return response()->json($new, 201);    
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = IngredientType::find($id);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IngredientType $ingredientType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIngredientTypeRequest $request, $id)
    {
        $item = IngredientType::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        
        $item->name = $request->name;
        $item->save();
        return response()->json($item, 204);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = IngredientType::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        
        $item->delete();
        return response()->json($item, 204);    
    }
}
