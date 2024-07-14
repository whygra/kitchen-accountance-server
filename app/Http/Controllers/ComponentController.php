<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Http\Requests\StoreComponentRequest;
use App\Http\Requests\UpdateComponentRequest;
use App\Models\ViewDTOs\ComponentViewDTO;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Console\Command;

class ComponentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = Component::get();
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
    public function store(StoreComponentRequest $request)
    {
        $new = new Component;
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
        $item = Component::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Component $component)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateComponentRequest $request, $id)
    {
        $item = Component::find($id);
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
        $item = Component::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        $item->delete();
        return response()->json($item, 204);
    }
}
