<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ingredient\DeleteIngredientRequest;
use App\Http\Requests\Ingredient\GetIngredientRequest;
use App\Http\Requests\IngredientTag\StoreIngredientTagWithIngredientsRequest;
use App\Http\Requests\IngredientTag\UpdateIngredientTagWithIngredientsRequest;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientTag;
use App\Models\Ingredient\IngredientIngredient;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngredientTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetIngredientRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->ingredient_tags()->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIngredientTagWithIngredientsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeIngredientTagSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества тегов ингредиентов."
            ], 400);
        
        $new = new IngredientTag;
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
        $item = $project->ingredient_tags()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти тег ингредиентов с id=".$item->id
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIngredientTagWithIngredientsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredient_tags()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти тег ингредиентов с id=".$item->id
            ], 404);
        $item->name = $request->name;
        $project->ingredient_tags()->save($item);
        return response()->json($item, 200);
    }

    /**
     * Display the specified resource.
     */

    public function index_loaded(GetIngredientRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->ingredient_tags()->with('ingredients.type')->get();
        return response()->json($all);
    }
     

    public function show_loaded(GetIngredientRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredient_tags()->with(['ingredients.type', 'updated_by_user'])->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти тегу ингредиентов с id=".$item->id
            ], 404);
        return response()->json($item);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_loaded(StoreIngredientTagWithIngredientsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeIngredientTagSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества тег ингредиентов."
            ], 400);
        $new = new IngredientTag;
        DB::transaction(function() use($request, $project_id, $new){
            $project = Project::find($project_id);
            $new->name = $request->name;
            $project->ingredient_tags()->save($new);
            $this->process_ingredients($new, $request);
        });
        
        return response()->json($new, 201);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update_loaded(UpdateIngredientTagWithIngredientsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredient_tags()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти тегу ингредиентов с id=".$item->id
            ], 404);
        
        DB::transaction(function() use($request, $project, $item){
            $item->name = $request->name;
            $project->ingredient_tags()->save($item);
            $this->process_ingredients($item, $request);
        });

        return response()->json($item, 200);
    }
    
    // удаление
    public function destroy(DeleteIngredientRequest $request, $project_id, $id) 
    {
        $project = Project::find($project_id);
        $item = $project->ingredient_tags()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти тегу ингредиентов с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }

    private function process_ingredients(IngredientTag $item, Request $request){
        
        $nNewIngredients = count(array_filter(
            $request['ingredients'],
            fn($i)=>($i['id'] ?? 0)==0
        ));

        $project = Project::find($request['project_id']);
        $freeSlots = $project->freeIngredientSlots();
        if($freeSlots<$nNewIngredients)
            return response()->json([
                'message' => "Невозможно добавить $nNewIngredients ингредиентов. Превышается лимит количества ингредиентов (осталось $freeSlots)."
            ], 400);

        $ingredients = [];
        foreach($request->ingredients as $i){
            $i = Ingredient::findOrNew($i['id']);

            if(empty($i->id)){
                $i->name = $i['name'];
                $i->project_id = $request['project_id'];
                $i->save();
            }

            $ingredients[$i->id] = [];
        }
    
        $sync = $item->ingredients()->sync($ingredients);
        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $item->touch();

    }
}
