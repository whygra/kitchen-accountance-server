<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ingredient\StoreIngredientRequest;
use App\Http\Requests\Ingredient\StoreIngredientWithProductsRequest;
use App\Http\Requests\Ingredient\UpdateIngredientRequest;
use App\Http\Requests\Ingredient\UpdateIngredientWithProductsRequest;
use App\Models\Ingredient\AbstractIngredient;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientCategory;
use App\Models\Ingredient\IngredientProduct;
use App\Models\Ingredient\SecondaryIngredient;
use App\Models\Ingredient\SecondaryIngredientIngredient;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
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
        $all = Ingredient::with(
                'ingredients_products.product', 'type', 'category'
            )->append('level')->get()->toArray();
        return response()->json($all);
    }

    public function show_loaded($id)
    {
        $item = Ingredient::with('ingredients_products.product', 'type', 'category')->append('level')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    // создание
    public function store_loaded(StoreIngredientWithProductsRequest $request)
    {
        $item = new Ingredient;
        
        // сначала создаем ингредиент - потом связи
        $item->name = $request->name;
        $item->type_id = $request->type_id;

        if($request->category_data_action == 'create'){
            $category = new IngredientCategory();
            $category->name = $request['category']['name'];
        } else {
            $category = IngredientCategory::find($request['category']['id']);
        }
        $item->category()->associate($category);

        $item->save();

        $this->process_products($item, $request);
        
        return response()->json($item, 201);
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

        $this->process_products($item, $request);

        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;

        if($request->category_data_action == 'create'){
            $category = new IngredientCategory();
            $category->name = $request['category']['name'];
        } else {
            $category = IngredientCategory::find($request['category']['id']);
        }
        $item->category()->associate($category);

        $item->save();

        return response()->json($item, 200);
    }
    
    private function process_products(Ingredient $item, FormRequest $request) {
        // для каждой связи компонент-продукт
        foreach ($request->ingredients_products as $ingredientProductData){

            $productData = $ingredientProductData['product'];
            $ingredientProductId = $ingredientProductData['id'];            

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
                    $ingredientProduct = $item->ingredients_products()->find($ingredientProductId);
                    $ingredientProduct->waste_percentage = $ingredientProductData['waste_percentage'];
                    $ingredientProduct->raw_content_percentage = $ingredientProductData['raw_content_percentage'];
                    break;
                case 'none':
                    // получение связи
                    $ingredientProduct = $item->ingredients_products()->find($ingredientProductId);
                    break;
                case 'delete':
                    // удаление связи
                    $ingredientProduct = $item->ingredients_products()->find($ingredientProductId);
                    $ingredientProduct->delete();
                    default:
                        continue 2;
            }

            // создание, обновление продукта
            switch($ingredientProductData['product_data_action']){
                case 'create':
                    // создание продукта
                    $product = new Product;
                    $product->name = $productData['name'] ?? '';
                    $product->save();
                    break;
                case 'none':
                    // получение продукта
                    $product = Product::find($productData['id']);
                    break;
                default:
                    continue 2;
            }

            $item->ingredients_products()->save(
                $ingredientProduct->product()->associate($product)->ingredient()->associate($item)
            );
        }
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
