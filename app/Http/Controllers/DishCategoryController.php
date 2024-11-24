<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dish\DeleteDishRequest;
use App\Http\Requests\Dish\GetDishRequest;
use App\Http\Requests\DishCategory\StoreDishCategoryWithDishesRequest;
use App\Http\Requests\DishCategory\UpdateDishCategoryWithDishesRequest;
use App\Http\Resources\Dish\DishCategoryResource;
use App\Models\Dish\DishCategory;
use App\Models\Dish\DishDish;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DishCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetDishRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->dish_categories()->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDishCategoryWithDishesRequest $request)
    {
        $new = new DishCategory;
        $new->name = $request->name;
        $new->project_id = $request->project_id;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetDishRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_categories()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию блюд с id=".$item->id
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishCategoryWithDishesRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_categories()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию блюд с id=".$item->id
            ], 404);
        $item->name = $request->name;
        $project->dish_categories()->save($item);
        return response()->json($item, 200);
    }

    /**
     * Display the specified resource.
     */

    public function index_loaded(GetDishRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->dish_categories()->with('dishes.ingredients')->get();
        return response()->json(DishCategoryResource::collection($all));
    }
     

    public function show_loaded(GetDishRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_categories()->with('dishes.ingredients')->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию блюд с id=".$item->id
            ], 404);
        return response()->json(new DishCategoryResource($item));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_loaded(StoreDishCategoryWithDishesRequest $request, $project_id)
    {
        $new = new DishCategory;
        DB::transaction(function() use($request, $project_id, $new){
            $project = Project::find($project_id);
            $new->name = $request->name;
            $project->dish_categories()->save($new);
            $this->process_dishes($new, $request);
        });
        
        return response()->json($new, 201);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update_loaded(UpdateDishCategoryWithDishesRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->dish_categories()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию блюд с id=".$item->id
            ], 404);
        
        DB::transaction(function() use($request, $project, $item){
            $item->name = $request->name;
            $project->dish_categories()->save($item);
            $this->process_dishes($item, $request);
        });

        return response()->json($item, 200);
    }
    
    // удаление
    public function destroy(DeleteDishRequest $request, $project_id, $id) 
    {
        $project = Project::find($project_id);
        $item = $project->dish_categories()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию блюд с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }

    private function process_dishes(DishCategory $item, Request $request){
        $item->dishes()->update(['category_id' => null]);
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
