<?php

namespace App\Http\Controllers;

use App\Models\ComponentType;
use App\Http\Requests\StoreComponentTypeRequest;
use App\Http\Requests\UpdateComponentTypeRequest;
use Exception;

class ComponentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = ComponentType::all();
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
    public function store(StoreComponentTypeRequest $request)
    {
        $new = new ComponentType;
        $new->name = $request->name;
        $new->save();
        return response()->json($new, 201);    
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = ComponentType::find($id);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ComponentType $componentType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateComponentTypeRequest $request, $id)
    {
        $item = ComponentType::find($id);
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
        $item = ComponentType::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        
        $item->delete();
        return response()->json($item, 204);    
    }
}
