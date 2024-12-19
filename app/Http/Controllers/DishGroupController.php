<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dish\DeleteDishRequest;
use App\Http\Requests\Dish\GetDishRequest;
use App\Http\Requests\DishGroup\StoreDishGroupWithDishesRequest;
use App\Http\Requests\DishGroup\UpdateDishGroupWithDishesRequest;
use App\Http\Resources\Dish\DishGroupResource;
use App\Models\Dish\DishGroup;
use App\Models\Dish\DishDish;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DishGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetDishRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->dish_groups()->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDishGroupWithDishesRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeDishGroupSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества групп блюд."
            ], 400);
        $new = new DishGroup;
        $new->name = $request->name;
        $new->project_id = $request->project_id;
        $project->dish_groups()->save($new);
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetDishRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_groups()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти группу блюд с id=".$item->id
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishGroupWithDishesRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_groups()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти группу блюд с id=".$item->id
            ], 404);
        $item->name = $request->name;
        $project->dish_groups()->save($item);
        return response()->json($item, 200);
    }

    /**
     * Display the specified resource.
     */

    public function index_loaded(GetDishRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->dish_groups()->with('dishes.ingredients')->get();
        return response()->json(DishGroupResource::collection($all));
    }
     

    public function show_loaded(GetDishRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_groups()->with('dishes.ingredients')->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти группу блюд с id=".$item->id
            ], 404);
        return response()->json(new DishGroupResource($item));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_loaded(StoreDishGroupWithDishesRequest $request, $project_id)
    {
        $new = new DishGroup;
        $project = Project::find($project_id);
        if($project->freeDishGroupSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества групп блюд."
            ], 400);

        DB::transaction(function() use($request, $project, $new){
            $new->name = $request->name;
            $project->dish_groups()->save($new);
            $this->process_dishes($new, $request);
        });
        
        return response()->json($new, 201);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update_loaded(UpdateDishGroupWithDishesRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_groups()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти группу блюд с id=".$item->id
            ], 404);
        
        DB::transaction(function() use($request, $project, $item){
            $item->name = $request->name;
            $project->dish_groups()->save($item);
            $this->process_dishes($item, $request);
        });

        return response()->json($item, 200);
    }
    
    // удаление
    public function destroy(DeleteDishRequest $request, $project_id, $id) 
    {
        $project = Project::find($project_id);
        $item = $project->dish_groups()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти группу блюд с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }

    private function process_dishes(DishGroup $item, Request $request){
        if($request['dishes']==null)
            return;
        
        $nNewDishes = count(array_filter(
            $request['dishes'],
            fn($p)=>($p['id'] ?? 0)==0
        ));

        $project = Project::find($request['propject_id']);
        $freeSlots = $project->freeDishSlots();
        if($freeSlots<$nNewDishes)
            return response()->json([
                'message' => "Невозможно добавить $nNewDishes блюд. Превышается лимит количества блюд (осталось $freeSlots)."
            ], 400);

        $item->dishes()->whereNotIn(
            'id', 
            array_map(fn($i)=>$i['id'], $request['dishes'])
        )->update(['group_id'=>null]);

        $project = Project::find($request->project_id);
        foreach($request['dishes'] as $i){
            $dish = $project->dishes()->findOrNew($i['id']);
            if(empty($dish->id)){
                $dish->name = $i['name'];
            }
            $item->dishes()->save($dish);
        }
    }
}
