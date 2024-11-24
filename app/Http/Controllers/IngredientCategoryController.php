<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ingredient\DeleteIngredientRequest;
use App\Http\Requests\Ingredient\GetIngredientRequest;
use App\Http\Requests\IngredientCategory\StoreIngredientCategoryWithIngredientsRequest;
use App\Http\Requests\IngredientCategory\UpdateIngredientCategoryWithIngredientsRequest;
use App\Models\Ingredient\IngredientCategory;
use App\Models\Ingredient\IngredientIngredient;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngredientCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetIngredientRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->ingredient_categories()->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIngredientCategoryWithIngredientsRequest $request)
    {
        $new = new IngredientCategory;
        $new->name = $request->name;
        $new->project_id = $request->project_id;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetIngredientRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredient_categories()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию ингредиентов с id=".$item->id
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIngredientCategoryWithIngredientsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredient_categories()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию ингредиентов с id=".$item->id
            ], 404);
        $item->name = $request->name;
        $project->ingredient_categories()->save($item);
        return response()->json($item, 200);
    }

    /**
     * Display the specified resource.
     */

    public function index_loaded(GetIngredientRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->ingredient_categories()->with('ingredients.type')->get();
        return response()->json($all);
    }
     

    public function show_loaded(GetIngredientRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredient_categories()->with('ingredients.type')->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию ингредиентов с id=".$item->id
            ], 404);
        return response()->json($item);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_loaded(StoreIngredientCategoryWithIngredientsRequest $request, $project_id)
    {
        $new = new IngredientCategory;
        DB::transaction(function() use($request, $project_id, $new){
            $project = Project::find($project_id);
            $new->name = $request->name;
            $project->ingredient_categories()->save($new);
            $this->process_ingredients($new, $request);
        });
        
        return response()->json($new, 201);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update_loaded(UpdateIngredientCategoryWithIngredientsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredient_categories()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию ингредиентов с id=".$item->id
            ], 404);
        
        DB::transaction(function() use($request, $project, $item){
            $item->name = $request->name;
            $project->ingredient_categories()->save($item);
            $this->process_ingredients($item, $request);
        });

        return response()->json($item, 200);
    }
    
    // удаление
    public function destroy(DeleteIngredientRequest $request, $project_id, $id) 
    {
        $project = Project::find($project_id);
        $item = $project->ingredient_categories()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию ингредиентов с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }

    private function process_ingredients(IngredientCategory $item, Request $request){
        $item->ingredients()->update(['category_id' => null]);
        $project = Project::find($request->project_id);
        foreach($request['ingredients'] as $i){
            $ingredient = $project->ingredients()->findOrNew($i['id']);
            if(empty($ingredient->id)){
                $ingredient->name = $i['name'];
                $ingredient->type_id = $i['type']['id'];
                $ingredient->is_item_measured = $i['is_item_measured'];
                $ingredient->item_weight = $i['item_weight'];
            }
            $item->ingredients()->save($ingredient);
        }
    }
}
