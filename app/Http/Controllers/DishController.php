<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDishRequest;
use App\Http\Requests\StoreDishWithIngredientsRequest;
use App\Http\Requests\StoreDishWithProductsRequest;
use App\Http\Requests\UpdateDishRequest;
use App\Http\Requests\UpdateDishWithIngredientsRequest;
use App\Http\Requests\UpdateDishWithProductsRequest;
use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\Product;
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
        $all = Dish::with('dishes_ingredients.ingredient.type')->get();
        return response()->json($all);
    }

    public function show_loaded($id)
    {
        $item = Dish::with('dishes_ingredients.ingredient.type')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    public function store_loaded(StoreDishWithIngredientsRequest $request)
    {
        
        $item = new Dish;

        // обновление данных компонента
        $item->name = $request->name;
        $item->image_path = $request->image_path ?? "";
        
        // для каждой связи блюдо-компонент
        foreach ($request->dishes_ingredients as $dishIngredientData){

            $ingredient_id = $dishIngredientData['ingredient']['id'];

            // создание, обновление компонента
            switch($dishIngredientData['ingredient_data_action']){
                case 'create':
                    // создание компонента
                    $ingredient = new Ingredient;
                    $ingredient->name = $dishIngredientData['ingredient']['name'] ?? '';
                    $type = IngredientType::find($dishIngredientData['ingredient']['type_id'] ?? 1);
                    $ingredient->type()->associate($type);
                    $ingredient->save();
                    break;
                case 'update':
                    // обновление компонента
                    $ingredient = Ingredient::find($ingredient_id);
                    $ingredient->name = $dishIngredientData['ingredient']['name'] ?? '';
                    $type = IngredientType::find($dishIngredientData['ingredient']['type_id'] ?? 1);
                    $ingredient->type()->associate($type);
                    $ingredient->save();
                    break;
                case 'none':
                    // получение компонента
                    $ingredient = Ingredient::find($ingredient_id);
                    break;
                default:
                    continue 2;
            }

            // создание связи
            $dishIngredient = new DishIngredient;
            $dishIngredient->ingredient_raw_weight = $dishIngredientData['ingredient_raw_weight'];
            $dishIngredient->waste_percentage = $dishIngredientData['waste_percentage'];
            
            $item->dishes_ingredients()->save($dishIngredient->ingredient()->associate($ingredient));
            $dishIngredient->save();
        }
        $item->save();
        return response()->json($item->with('dishes_ingredients.ingredient', 'type'), 201);
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

        // для каждой связи блюдо-компонент
        foreach ($request->dishes_ingredients as $dishIngredientData){

            $ingredient_id = $dishIngredientData['ingredient']['id'];
            $dish_ingredient_id = $dishIngredientData['id'];            

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
                    $dishIngredient = DishIngredient::find($dish_ingredient_id);
                    $dishIngredient->waste_percentage = $dishIngredientData['waste_percentage'];
                    $dishIngredient->ingredient_raw_weight = $dishIngredientData['ingredient_raw_weight'];
                    break;
                case 'none':
                    // получение связи
                    $dishIngredient = DishIngredient::find($dish_ingredient_id);
                    break;
                case 'delete':
                    // удаление связи
                    $dishIngredient = DishIngredient::find($dish_ingredient_id);
                    $dishIngredient->delete();
                default:
                    continue 2;
            }

            // создание, обновление продукта
            switch($dishIngredientData['product_data_action']){
                case 'create':
                    // создание продукта
                    $ingredient = new Ingredient;
                    $ingredient->name = $dishIngredientData['ingredient']['name'] ?? '';
                    $ingredient->type_id = $dishIngredientData['ingredient']['type_id'];
                    $ingredient->save();
                    break;
                case 'none':
                    // получение продукта
                    $ingredient = Ingredient::find($ingredient_id);
                    break;
                default:
                    continue 2;
            }

            $item->dishes_ingredients()->save($dishIngredient->ingredient()->associate($ingredient)->dish()->associate($item));
        }

        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $item->image_path = $request->image_path;
        $item->save();

        return response()->json($item->with('dishes_ingredients.ingredient', 'type'), 200);
    }

    public function show_with_purchase_options($id)
    {
        $item = Dish::with('dishes_ingredients.ingredient.ingredients_products.product.purchase_options.distributor')->find($id);
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
