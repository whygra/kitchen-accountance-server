<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dish\DeleteDishRequest;
use App\Http\Requests\Dish\GetDishRequest;
use App\Http\Requests\Dish\StoreDishRequest;
use App\Http\Requests\Dish\StoreDishWithIngredientsRequest;
use App\Http\Requests\Dish\StoreDishWithProductsRequest;
use App\Http\Requests\Dish\UpdateDishRequest;
use App\Http\Requests\Dish\UpdateDishWithIngredientsRequest;
use App\Http\Requests\Dish\UpdateDishWithProductsRequest;
use App\Http\Resources\Dish\DishResource;
use App\Http\Resources\Dish\DishWithPurchaseOptionsResource;
use App\Models\Ingredient\Ingredient;
use App\Models\Dish\Dish;
use App\Models\Dish\DishCategory;
use App\Models\Dish\DishIngredient;
use App\Models\Ingredient\SecondaryIngredient;
use App\Models\Product\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class DishController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = Dish::all();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDishRequest $request)
    {
        $new = new Dish;
        $new->name = $request->name;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetDishRequest $request, $id)
    {
        $item = Dish::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishRequest $request, $id)
    {
        $item = Dish::find($id);
        $item->name = $request->name;
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        $item->name = $request->name;
        $item->save();
            return response()->json($item, 200);
    }
    
    public function index_loaded(GetDishRequest $request)
    {
        $all = Dish::with(
            'ingredients.type',
            'ingredients.category',
            'category'
        )->get();
        return response()->json(DishResource::collection($all));
    }

    public function show_loaded(GetDishRequest $request, $id)
    {
        $item = Dish::with(
            'ingredients.type', 
            'ingredients.category',
            'category'
        )->find($id);

        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json(new DishResource($item));
    }

    public function store_loaded(StoreDishWithIngredientsRequest $request)
    {
        
        $item = new Dish;

        // обновление данных блюда
        $category = DishCategory::findOrNew($request['category']['id']);
        if(empty($category->id)){
            $category->name = $request['category']['name'];
            $category->save();
        }
        $item->category()->associate($category);
        $item->name = $request->name;
        $item->image_path = $request->image_path ?? "";

        $item->save();
        
        $this->process_ingredients($item, $request);

        return response()->json($item, 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update_loaded(UpdateDishWithIngredientsRequest $request, $id)
    {
        $item = Dish::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Компонент с id=$id не найден"
            ], 404);

        // обновление данных блюда
        $category = DishCategory::findOrNew($request['category']['id']);
        if(empty($category->id)){
            $category->name = $request['category']['name'];
            $category->save();
        }
        $item->category()->associate($category);
        $item->name = $request->name;
        $item->image_path = $request->image_path ?? "";

        $item->save();
        
        $this->process_ingredients($item, $request);

        return response()->json($item, 200);
    }


    private function process_ingredients(Dish $item, FormRequest $request) {
        $ingredients = [];
        foreach($request->ingredients as $i){
            $ingredient = Ingredient::findOrNew($i['id']);

            if(empty($ingredient->id)){
                $ingredient->type_id = $i['type']['id'];
                $ingredient->name = $i['name'];
                $ingredient->item_weight = $i['item_weight'];
                $ingredient->save();
            }

            $ingredients[$ingredient->id] = [
                'waste_percentage'=>$i['waste_percentage'],
                'ingredient_amount'=>$i['ingredient_amount']
            ];
        }
    
        $item->ingredients()->sync($ingredients);
    }

    // блюдо с позициями закупки
    public function show_with_purchase_options(GetDishRequest $request, $id)
    {
        $item = Dish::with(
            'ingredients.products.purchase_options.distributor',
            )->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json(new DishWithPurchaseOptionsResource($item));
    }

    // удаление блюда и связей блюдо-компонент
    public function destroy(DeleteDishRequest $request, $id)
    {
        $item = Dish::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти Блюдо с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }
}
