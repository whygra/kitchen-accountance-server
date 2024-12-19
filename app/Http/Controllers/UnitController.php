<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\DeleteUnitRequest;
use App\Http\Requests\Unit\GetUnitRequest;
use App\Models\Distributor\Unit;
use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\Project;
use Exception;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetUnitRequest $request, $project_id)
    {
        $project = Project::with(['updated_by_user'])->find($project_id);
        $all = $project->units()->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeUnitSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества позиций закупки."
            ], 400);
        $new = new Unit;
        $new->long = $request->long;
        $new->short = $request->short;
        $project->units()->save($new);
        return response()->json($new, 204);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetUnitRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->units()->find($id);
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->units()->find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->short = $request->short;
        $item->long = $request->long;

        $item->save();
        return response()->json($item, 204);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteUnitRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->units()->find($id);
        if(empty($item))
            return response()->json([
                'message' => ''
            ], 404);

        $item->delete();
        return response()->json($item, 204);
    }
}
