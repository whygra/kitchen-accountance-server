<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\DeleteUnitRequest;
use App\Http\Requests\Unit\GetUnitRequest;
use App\Models\Distributor\Unit;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use Exception;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetUnitRequest $request)
    {
        $all = Unit::all();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request)
    {
        $new = new Unit;
        $new->long = $request->long;
        $new->short = $request->short;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetUnitRequest $request, $id)
    {
        $item = Unit::find($id);
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, $id)
    {
        $item = Unit::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->name = $request->name;

        $item->save();
        return response()->json($item, 204);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteUnitRequest $request, $id)
    {
        $item = Unit::find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->delete();
        return response()->json($item, 204);
    }
}
