<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dish\StoreDishRequest;
use App\Http\Requests\Dish\StoreDishWithIngredientsRequest;
use App\Http\Requests\Dish\StoreDishWithProductsRequest;
use App\Http\Requests\Dish\UpdateDishRequest;
use App\Http\Requests\Dish\UpdateDishWithIngredientsRequest;
use App\Http\Requests\Dish\UpdateDishWithProductsRequest;
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show($id)
    {
        $item = Dish::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dish $dish)
    {
        //
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
    
    public function index_loaded()
    {
        $all = Dish::with(
            'dishes_primary_ingredients.ingredient.type', 
            'dishes_secondary_ingredients.ingredient.type', 
            'category'
        )->get();
        return response()->json($all);
    }

    public function show_loaded($id)
    {
        $item = Dish::with(
            'dishes_primary_ingredients.ingredient.type', 
            'dishes_secondary_ingredients.ingredient.type', 
            'category'
        )->find($id);

        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    public function store_loaded(StoreDishWithIngredientsRequest $request)
    {
        
        $item = new Dish;

        // обновление данных блюда
        if($request->category_data_action == 'create'){
            $category = new DishCategory();
            $category->name = $request['category']['name'];
        } else {
            $category = DishCategory::find($request['category']['id']);
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

        $this->process_ingredients($item, $request);

        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;

        if($request->category_data_action == 'create'){
            $category = new DishCategory();
            $category->name = $request['category']['name'];
        } else {
            $category = DishCategory::find($request['category']['id']);
        }

        $item->category()->associate($category);
        $item->image_path = $request->image_path;
        $item->save();

        return response()->json($item, 200);
    }

    private function process_ingredients(Dish $item, FormRequest $request) {
        // для каждой связи блюдо-компонент
        foreach ($request->dishes_ingredients as $dishIngredientData){

            $ingredientData = $dishIngredientData['ingredient'];
            $dishIngredientId = $dishIngredientData['id'];            

            // создание, обновление связи
            switch($dishIngredientData['data_action']){
                case 'create':
                    // создание связи
                    $dishIngredient = new DishIngredient;
                    $dishIngredient->waste_percentage = $dishIngredientData['waste_percentage'];
                    $dishIngredient->weight = $dishIngredientData['weight'];
                    break;
                case 'update':
                    // обновление связи
                    $dishIngredient = $item->dishes_ingredients()->find($dishIngredientId);
                    $dishIngredient->waste_percentage = $dishIngredientData['waste_percentage'];
                    $dishIngredient->ingredient_raw_weight = $dishIngredientData['ingredient_raw_weight'];
                    break;
                case 'none':
                    // получение связи
                    $dishIngredient = $item->dishes_ingredients()->find($dishIngredientId);
                    break;
                case 'delete':
                    // удаление связи
                    $dishIngredient = $item->dishes_ingredients()->find($dishIngredientId);
                    $dishIngredient->delete();
                default:
                    continue 2;
            }

            // создание, обновление ингредиента
            switch($dishIngredientData['ingredient_data_action']){
                case 'create':
                    // создание ингредиента
                    $ingredient = new Ingredient;
                    $ingredient->name = $ingredientData['name'] ?? '';
                    $ingredient->type_id = $ingredientData['type_id'];
                    $ingredient->save();
                    break;
                case 'none':
                    // получение ингредиента
                    $ingredient = Ingredient::find($ingredientData['id']);
                    break;
                default:
                    continue 2;
            }

            $item->dishes_ingredients()->save(
                $dishIngredient->ingredient()->associate($ingredient)->dish()->associate($item)
            );
        }
    }

    // блюдо с позициями закупки
    public function show_with_purchase_options($id)
    {
        $item = Dish::with(
            'dishes_ingredients.ingredient.ingredients_products.product.purchase_options.distributor',
            )->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    // удаление блюда и связей блюдо-компонент
    public function destroy($id)
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
