<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\StoreIngredientWithProductsRequest;
use App\Http\Requests\UpdateIngredientRequest;
use App\Http\Requests\UpdateIngredientWithProductsRequest;
use App\Models\Ingredient;
use App\Models\IngredientProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource. 
     */
    public function index()
    {
        $all = Ingredient::get();
        return response()->json($all);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIngredientRequest $request)
    {
        $new = new Ingredient;
        $new->name = $request->name;
        $new->type_id = $request->type_id;

        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = Ingredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ingredient $ingredient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateIngredientRequest $request, $id)
    {
        $item = Ingredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $item->save();
        return response()->json($item, 200);
        
    }

    public function index_loaded()
    {
        $all = Ingredient::with('ingredients_products.product', 'type')->get();
        return response()->json($all);
    }

    public function show_loaded($id)
    {
        $item = Ingredient::with('ingredients_products.product', 'type')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    public function store_loaded(StoreIngredientWithProductsRequest $request)
    {
        
        $item = new Ingredient;
        
        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        
        // для каждой связи компонент-продукт
        foreach ($request->ingredients_products as $ingredientProductData){

            $product_id = $ingredientProductData['product']['name'];

            // создание, обновление продукта
            switch($ingredientProductData['product_data_action']){
                case 'create':
                    // создание продукта
                    $product = new Product;
                    $product->name = $ingredientProductData['product']['name'] ?? '';
                    $product->save();
                    break;
                case 'update':
                    // обновление продукта
                    $product = Product::find($product_id);
                    $product->name = $ingredientProductData['product']['name'] ?? '';
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
    public function update_loaded(UpdateIngredientWithProductsRequest $request, $id)
    {
        $item = Ingredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Компонент с id=$id не найден"
            ], 404);

        // для каждой связи компонент-продукт
        foreach ($request->ingredients_products as $ingredientProductData){

            $product_id = $ingredientProductData['product']['id'];
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
                    $product->name = $ingredientProductData['product']['name'] ?? '';
                    $product->save();
                    break;
                case 'update':
                    // обновление продукта
                    $product = Product::find($product_id);
                    $product->name = $ingredientProductData['product']['name'] ?? '';
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
        return response()->json($item);
    }
}
