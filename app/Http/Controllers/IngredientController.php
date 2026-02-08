<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ingredient\DeleteIngredientRequest;
use App\Http\Requests\Ingredient\GetIngredientRequest;
use App\Http\Requests\Ingredient\GetIngredientWithProductsRequest;
use App\Http\Requests\Ingredient\StoreIngredientRequest;
use App\Http\Requests\Ingredient\StoreIngredientWithProductsRequest;
use App\Http\Requests\Ingredient\UpdateIngredientRequest;
use App\Http\Requests\Ingredient\UpdateIngredientWithProductsRequest;
use App\Http\Resources\Dish\DishIngredientWithPurchaseOptionsResource;
use App\Http\Resources\Ingredient\IngredientResource;
use App\Http\Resources\Ingredient\IngredientWithPurchaseOptionsResource;
use App\Http\Resources\Ingredient\MinIngredientResource;
use App\Models\Ingredient\AbstractIngredient;
use App\Models\Ingredient\Ingredient;
use App\Models\Product\Product;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index(GetIngredientRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->ingredients()->with('type')->get();
        return response()->json($all);
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIngredientRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeDishSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества ингредиентов."
            ], 400);

        $new = new Ingredient;
        $new->name = $request->name;
        $new->type_id = $request->type_id;

        $project->ingredients()->save($new);
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetIngredientRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredients()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Ингредиент с id=$id не найден"
            ], 404);
            
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateIngredientRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredients()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Ингредиент с id=$id не найден"
            ], 404);
            
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $project->ingredients()->save($item);
        return response()->json($item, 200);
    }

    public function index_loaded(GetIngredientWithProductsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        $all = $project->ingredients()->with(
            'products', 'type', 'tags',
        )->get();
        return response()->json(IngredientResource::collection($all));
    }

    public function show_loaded(GetIngredientRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredients()->with([
            'products', 'products.purchase_options.distributor', 'type', 'tags', 'dishes', 'updated_by_user'
            ])->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Ингредиент с id=$id не найден"
            ], 404);
            
        return response()->json(new MinIngredientResource($item));
    }

    public function show_with_purchase_options(GetIngredientRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredients()->with([
            'products','products.purchase_options.distributor', 'type', 'tags', 'dishes', 'updated_by_user'
            ])->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Ингредиент с id=$id не найден"
            ], 404);
            
        return response()->json(new IngredientWithPurchaseOptionsResource($item));
    }
    
    // создание
    public function store_loaded(StoreIngredientWithProductsRequest $request, $project_id)
    {
        $project = Project::find($project_id);
        if($project->freeDishSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества ингредиентов."
            ], 400);

        $item = new Ingredient;
        DB::transaction(function() use ($request, $item, $project) {
            // сначала создаем ингредиент - потом связи
            $item->name = $request['name'];
            $item->description = $request['description'];
            if($request['is_item_measured']){
                $item->item_weight = $request['item_weight'];
                $item->is_item_measured =$request['is_item_measured'];
            } else {
                $item->item_weight = 1;
            }
            $item->type_id = $request['type']['id'];

            $project->ingredients()->save($item);

            $this->process_products($item, $request);
            $this->process_ingredients($item, $request);
            $this->process_tags($item, $request);
            
            $item->total_gross_weight = $item->getAtrTotalGrossWeight();
            $item->total_net_weight = $item->getAtrTotalNetWeight();

            $item->save();
        });
        
        return response()->json($item, 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update_loaded(UpdateIngredientWithProductsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = Ingredient::where('project_id', $request['project_id'])->find($id);

        if (empty($item))
            return response()->json([
                'message' => "Компонент с id=$id не найден"
            ], 404);

        DB::transaction(function() use ($request, $project, $item) {
            $this->process_products($item, $request);
            $this->process_ingredients($item, $request);
            $this->process_tags($item, $request);

            // обновление данных компонента
            $item->name = $request->name;
            $item->description = $request['description'];
            $item->type_id = $request['type']['id'];
            
            $item->is_item_measured =$request['is_item_measured'];
            if($request['is_item_measured']){
                $item->item_weight = $request['item_weight'];
            } else {
                $item->item_weight = 1;
            }


            $item->total_gross_weight = $item->getAtrTotalGrossWeight();
            $item->total_net_weight = $item->getAtrTotalNetWeight();
            
            $item->save();
            
        });

        return response()->json($item, 200);
    }
    
    private function process_products(Ingredient $item, FormRequest $request) {
        $project = Project::find($request['project_id']);
        
        $nNewProducts = count(array_filter(
            $request['products'],
            fn($p)=>($p['id'] ?? 0)==0
        ));

        $freeSlots = $project->freeProductSlots();
        if($freeSlots<$nNewProducts)
            return response()->json([
                'message' => "Невозможно добавить $nNewProducts продуктов. Превышается лимит количества продуктов (осталось $freeSlots)."
            ], 400);

        $products = [];
        foreach($request->products as $p){
            $product = Product::findOrNew($p['id']);

            if(empty($product->id)){
                $product->name = $p['name'];
                $product->project_id = $request['project_id'];
                $product->save();
            }

            $products[$product->id] = [
                'net_weight'=>$p['net_weight'],
                'gross_weight'=>$p['gross_weight']
            ];
        }
    
        $sync = $item->products()->sync($products);
        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $item->touch();

    }
    
    private function process_ingredients(Ingredient $item, FormRequest $request) {
        $project = Project::find($request['project_id']);
        
        $nNewIngredients = count(array_filter(
            $request['ingredients'],
            fn($p)=>($p['id'] ?? 0)==0
        ));

        $freeSlots = $project->freeProductSlots();
        if($freeSlots<$nNewIngredients)
            return response()->json([
                'message' => "Невозможно добавить $nNewIngredients ингредиентов. Превышается лимит количества (осталось $freeSlots)."
            ], 400);

        $ingredients = [];
        foreach($request->ingredients as $p){
            $ingredient = $project->ingredients()->findOrNew($p['id']);

            if(empty($ingredient->id)){
                $ingredient->name = $p['name'];
                $ingredient->type_id = $p['type']['id'];
                $ingredient->project_id = $request['project_id'];
                $ingredient->save();
            }

            $ingredients[$ingredient->id] = [
                'amount'=>$p['amount'],
                'net_weight'=>$p['net_weight']
            ];
        }
    
        $sync = $item->ingredients()->sync($ingredients);
        
        $item->total_gross_weight = $item->atr_total_gross_weight;
        $item->total_net_weight = $item->atr_total_net_weight;

        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $item->touch();

    }
    // теги
    private function process_tags(Ingredient $item, FormRequest $request) {
        $project = Project::find($request->project_id);
        
        $nNewTags = count(array_filter(
            $request['tags'],
            fn($p)=>($p['id'] ?? 0)==0
        ));

        $freeSlots = $project->freeIngredientTagSlots();
        if($freeSlots<$nNewTags)
            return response()->json([
                'message' => "Невозможно добавить $nNewTags тэгов. Превышается лимит (осталось $freeSlots)."
            ], 400);
            
        $tags = [];
            
        foreach($request->tags as $t){
            $tag = $project->ingredient_tags()->where('name', $t['name'])->first();

            if(empty($tag)){
                $tag = $project->ingredient_tags()->create();
                $tag->name = $t['name'];
                $tag->save();
            }

            array_push($tags, $tag->id);
        }

        $item->tags()->sync($tags);

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteIngredientRequest $request, $project_id, $id)
    {        
        $project = Project::find($project_id);
        $item = $project->ingredients()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти Ингредиент с id=$id"
            ], 404);
            
        $item->delete();
        return response()->json($item);
    }
}
