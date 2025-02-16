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
use App\Models\Ingredient\AbstractIngredient;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientCategory;
use App\Models\Ingredient\IngredientGroup;
use App\Models\Ingredient\IngredientProduct;
use App\Models\Ingredient\SecondaryIngredient;
use App\Models\Ingredient\SecondaryIngredientIngredient;
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
        $all = $project->ingredients()->get();
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
            'products.category', 'type', 'category',
        )->get();
        return response()->json(IngredientResource::collection($all));
    }

    public function show_loaded(GetIngredientRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->ingredients()->with([
            'products.category', 'products.purchase_options.distributor', 'type', 'category', 'dishes.category', 'updated_by_user'
            ])->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Ингредиент с id=$id не найден"
            ], 404);
            
        return response()->json(new IngredientResource($item));
    }

    public function show_with_purchase_options(GetIngredientRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->infgredients()->with([
            'products.category','products.purchase_options.distributor', 'type', 'category', 'dishes.category', 'updated_by_user'
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
            }
            $item->type_id = $request['type']['id'];

            $group = null;
            if($request['group']['id'] !== 0)
                $group = $project->ingredient_groups()->find($request['group']['id']);
            else if(!empty($request['group']['name']))
                $group = $project->ingredient_groups()->create([
                    'name'=>$request['group']['name'],
                ]);

            $item->group()->associate($group);

            $category = null;
            if($request['category']['id'] !== 0)
                $category = $project->ingredient_categories()->find($request['category']['id']);
            else if(!empty($request['category']['name']))
                $category = $project->ingredient_categories()->create([
                    'name'=>$request['category']['name'],
                ]);

            $item->category()->associate($category);

            $project->ingredients()->save($item);

            $this->process_products($item, $request);
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

            // обновление данных компонента
            $item->name = $request->name;
            $item->description = $request['description'];
            $item->type_id = $request['type']['id'];
            
            if($request['is_item_measured']){
                $item->item_weight = $request['item_weight'];
                $item->is_item_measured =$request['is_item_measured'];
            }

            $group = null;
            if($request['group']['id'] !== 0)
                $group = $project->ingredient_groups()->find($request['group']['id']);
            else if(!empty($request['group']['name']))
                $group = $project->ingredient_groups()->create([
                    'name'=>$request['group']['name'],
                ]);

            $item->group()->associate($group);

            $category = null;
            if($request['category']['id'] !== 0)
                $category = $project->ingredient_categories()->find($request['category']['id']);
            else if(!empty($request['category']['name']))
                $category = $project->ingredient_categories()->create(['name'=>$request['category']['name']]);

            $item->category()->associate($category);

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
                'waste_percentage'=>$p['waste_percentage'],
                'raw_product_weight'=>$p['raw_product_weight']
            ];
        }
    
        $sync = $item->products()->sync($products);
        if(!empty($sync['attached'])||!empty($sync['detached'])||!empty($sync['updated']))
            $item->touch();

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
