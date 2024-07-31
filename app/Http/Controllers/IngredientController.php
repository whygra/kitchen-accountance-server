<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\UpdateIngredientRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Console\Command;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $all = Ingredient::get();
        return response()->json($all);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIngredientRequest $request)
    {
        $new = new Ingredient;
        $new->name = $request->name;
        $new->type_id = $request->type_id;

        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = Ingredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ingredient $ingredient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateIngredientRequest $request, $id)
    {
        $item = Ingredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $item->save();
        return response()->json($item, 200);
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {        
        $item = Ingredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        $item->delete();
        return response()->json($item, 204);
    }
}
