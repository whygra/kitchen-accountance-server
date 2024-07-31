<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIngredientWithProductsRequest;
use App\Http\Requests\UpdateIngredientWithProductsRequest;
use App\Models\Ingredient;
use App\Models\IngredientProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class IngredientWithProductsController extends Controller
{
    public function index()
    {
        $all = Ingredient::with('ingredients_products.product', 'type')->get();
        return response()->json($all);
    }

    public function show($id)
    {
        $item = Ingredient::with('ingredients_products.product', 'type')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    public function store(StoreIngredientWithProductsRequest $request)
    {
        
        $item = new Ingredient;
        
        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $item->save();
        
        // для каждой связи компонент-продукт
        foreach ($request->ingredients_products as $ingredientProductData){

            $product_id = $ingredientProductData['product_id'];

            // создание, обновление продукта
            switch($ingredientProductData['product_data_action']){
                case 'create':
                    // создание продукта
                    $product = new Product;
                    $product->name = $ingredientProductData['product_name'] ?? '';
                    $product->save();
                    break;
                case 'update':
                    // обновление продукта
                    $product = Product::find($product_id);
                    $product->name = $ingredientProductData['product_name'] ?? '';
                    $product->save();
                    break;
                case 'none':
                    // получение продукта
                    $product = Product::find($product_id);
                    break;
                default:
                    continue 2;
            }

            // создание связи
            $ingredientProduct = new IngredientProduct;
            $ingredientProduct->raw_content_percentage = $ingredientProductData['raw_content_percentage'];
            $ingredientProduct->waste_percentage = $ingredientProductData['waste_percentage'];
            $item->ingredients_products()->save($ingredientProduct->product()->associate($product));
        }

        $item->save();

        return response()->json($item->with('ingredients_products.product', 'type'), 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateIngredientWithProductsRequest $request, $id)
    {
        $item = Ingredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Компонент с id=$id не найден"
            ], 404);

        // для каждой связи компонент-продукт
        foreach ($request->ingredients_products as $ingredientProductData){

            $product_id = $ingredientProductData['product_id'];
            $ingredient_product_id = $ingredientProductData['id'];            

            // создание, обновление связи
            switch($ingredientProductData['data_action']){
                case 'create':
                    // создание связи
                    $ingredientProduct = new IngredientProduct;
                    $ingredientProduct->waste_percentage = $ingredientProductData['waste_percentage'];
                    $ingredientProduct->raw_content_percentage = $ingredientProductData['raw_content_percentage'];
                    break;
                case 'update':
                    // обновление связи
                    $ingredientProduct = IngredientProduct::find($ingredient_product_id);
                    $ingredientProduct->waste_percentage = $ingredientProductData['waste_percentage'];
                    $ingredientProduct->raw_content_percentage = $ingredientProductData['raw_content_percentage'];
                    break;
                case 'none':
                    // получение связи
                    $ingredientProduct = IngredientProduct::find($ingredient_product_id);
                    break;
                case 'delete':
                    // удаление связи
                    $ingredientProduct = IngredientProduct::find($ingredient_product_id);
                    $ingredientProduct->delete();
                default:
                    continue 2;
            }

            // создание, обновление продукта
            switch($ingredientProductData['product_data_action']){
                case 'create':
                    // создание продукта
                    $product = new Product;
                    $product->name = $ingredientProductData['product_name'] ?? '';
                    $product->save();
                    break;
                case 'update':
                    // обновление продукта
                    $product = Product::find($product_id);
                    $product->name = $ingredientProductData['product_name'] ?? '';
                    $product->save();
                    break;
                case 'none':
                    // получение продукта
                    $product = Product::find($product_id);
                    break;
                default:
                    continue 2;
            } 

            $item->ingredients_products()->save($ingredientProduct->product()->associate($product));
        }
        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $item->save();

        return response()->json($item->with('ingredients_products.product', 'type'), 200);
    }

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {        
        $item = Ingredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти Ингредиент с id=$id"
            ], 404);
            
        $item->delete();
        return response()->json($item, 200);
    }
}
