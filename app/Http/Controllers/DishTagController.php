<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dish\DeleteDishRequest;
use App\Http\Requests\Dish\GetDishRequest;
use App\Http\Requests\DishTag\StoreDishTagWithDishesRequest;
use App\Http\Requests\DishTag\UpdateDishTagWithDishesRequest;
use App\Http\Resources\Dish\DishTagResource;
use App\Models\Dish\Dish;
use App\Models\Dish\DishTag;
use App\Models\Dish\DishDish;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DishTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetDishRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->dish_tags()->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDishTagWithDishesRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeDishTagSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества тегов блюд."
            ], 400);
        $new = new DishTag;
        $new->name = $request->name;
        $new->project_id = $request->project_id;
        $project->dish_tags()->save($new);
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetDishRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_tags()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти тег блюд с id=".$item->id
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishTagWithDishesRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_tags()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти тег блюд с id=".$item->id
            ], 404);
        $item->name = $request->name;
        $project->dish_tags()->save($item);
        return response()->json($item, 200);
    }

    /**
     * Display the specified resource.
     */

    public function index_loaded(GetDishRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->dish_tags()->with('dishes')->get();
        return response()->json(DishTagResource::collection($all));
    }
     

    public function show_loaded(GetDishRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_tags()->with('dishes')->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти тег блюд с id=".$item->id
            ], 404);
        return response()->json(new DishTagResource($item));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_loaded(StoreDishTagWithDishesRequest $request, $project_id)
    {
        $new = new DishTag;
        $project = Project::find($project_id);
        if($project->freeDishTagSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества тегов блюд."
            ], 400);

        DB::transaction(function() use($request, $project, $new){
            $new->name = $request->name;
            $project->dish_tags()->save($new);
            $this->process_dishes($new, $request);
        });
        
        return response()->json($new, 201);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update_loaded(UpdateDishTagWithDishesRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_tags()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти тег блюд с id=".$item->id
            ], 404);
        
        DB::transaction(function() use($request, $project, $item){
            $item->name = $request->name;
            $project->dish_tags()->save($item);
            $this->process_dishes($item, $request);
        });

        return response()->json($item, 200);
    }
    
    // удаление
    public function destroy(DeleteDishRequest $request, $project_id, $id) 
    {
        $project = Project::find($project_id);
        $item = $project->dish_tags()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти тег блюд с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }

    private function process_dishes(DishTag $item, Request $request){
        $nNewDishes = count(array_filter(
            $request['dishes'],
            fn($d)=>($d['id'] ?? 0)==0
        ));

        $project = Project::find($request['project_id']);
        $freeSlots = $project->freeDishSlots();
        if($freeSlots<$nNewDishes)
            return response()->json([
                'message' => "Невозможно добавить $nNewDishes блюд. Превышается лимит (осталось $freeSlots)."
            ], 400);

        $dishes = [];
        foreach($request->dishes as $d){
            $d = Dish::findOrNew($d['id']);

            if(empty($d->id)){
                $d->name = $d['name'];
                $d->project_id = $request['project_id'];
                $d->save();
            }

            $dishes[$d->id] = [
            ];
        }
    
        $sync = $item->dishes()->sync($dishes);
        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $item->touch();
    }
}
