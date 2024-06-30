<?php

namespace App\Http\Controllers;

use App\Models\DishComponent;
use App\Http\Requests\StoreDishComponentRequest;
use App\Http\Requests\UpdateDishComponentRequest;
use Exception;

class DishComponentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = DishComponent::all();
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
    public function store(StoreDishComponentRequest $request)
    {
        $new = new DishComponent;
        $new->dish_id = $request->dish_id;
        $new->component_id = $request->component_id;
        $new->component_weight = $request->component_weight;
        $new->waste_percentage = $request->waste_percentage;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = DishComponent::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DishComponent $dishComponent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishComponentRequest $request, $id)
    {
        $item = DishComponent::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
        
        $item->dish_id = $request->dish_id;
        $item->component_id = $request->component_id;
        $item->component_weight = $request->component_weight;
        $item->waste_percentage = $request->waste_percentage;
        $item->save();
        return response()->json($item, 204);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = DishComponent::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
        
        $item->delete();
        return response()->json($item, 204);    
    }
}
