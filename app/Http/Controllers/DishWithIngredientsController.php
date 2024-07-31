<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDishWithIngredientsRequest;
use App\Http\Requests\StoreDishWithProductsRequest;
use App\Http\Requests\UpdateDishWithIngredientsRequest;
use App\Http\Requests\UpdateDishWithProductsRequest;
use App\Models\Ingredient;
use App\Models\IngredientType;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\Product;
use Illuminate\Http\Request;

class DishWithIngredientsController extends Controller
{
    public function index()
    {
        $all = Dish::with('dishes_ingredients.ingredient.type')->get();
        return response()->json($all);
    }

    public function show($id)
    {
        $item = Dish::with('dishes_ingredients.ingredient.type')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    public function store(StoreDishWithIngredientsRequest $request)
    {
        
        $item = new Dish;

        // обновление данных компонента
        $item->name = $request->name;
        $item->image_path = $request->image_path ?? "";
        $item->save();
        
        // для каждой связи блюдо-компонент
        foreach ($request->dishes_ingredients as $dishIngredientData){

            $ingredient_id = $dishIngredientData['ingredient_id'];

            // создание, обновление компонента
            switch($dishIngredientData['ingredient_data_action']){
                case 'create':
                    // создание компонента
                    $ingredient = new Ingredient;
                    $ingredient->name = $dishIngredientData['ingredient_name'] ?? '';
                    $type = IngredientType::find($dishIngredientData['ingredient_type_id'] ?? 1);
                    $ingredient->type()->associate($type);
                    $ingredient->save();
                    break;
                case 'update':
                    // обновление компонента
                    $ingredient = Ingredient::find($ingredient_id);
                    $ingredient->name = $dishIngredientData['ingredient_name'] ?? '';
                    $type = IngredientType::find($dishIngredientData['ingredient_type_id'] ?? 1);
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
        
        return response()->json($item->with('dishes_ingredients.ingredient', 'type'), 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateDishWithIngredientsRequest $request, $id)
    {
        $item = Dish::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Компонент с id=$id не найден"
            ], 404);

        // для каждой связи блюдо-компонент
        foreach ($request->dishes_ingredients as $dishIngredientData){

            $ingredient_id = $dishIngredientData['ingredient_id'];
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
                    $ingredient->name = $dishIngredientData['ingredient_name'] ?? '';
                    $ingredient->type_id = $dishIngredientData['ingredient_type_id'];
                    $ingredient->save();
                    break;
                case 'update':
                    // обновление продукта
                    $ingredient = Ingredient::find($ingredient_id);
                    $ingredient->name = $dishIngredientData['ingredient_name'] ?? '';
                    $ingredient->type_id = $dishIngredientData['ingredient_type_id'] ?? 1;
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

    // удаление блюда и связей блюдо-компонент
    public function destroy($id) 
    {
        $item = Dish::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти Блюдо с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item, 204);
    }
}
