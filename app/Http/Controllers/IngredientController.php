<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ingredient\DeleteIngredientRequest;
use App\Http\Requests\Ingredient\GetIngredientRequest;
use App\Http\Requests\Ingredient\GetIngredientWithProductsRequest;
use App\Http\Requests\Ingredient\StoreIngredientRequest;
use App\Http\Requests\Ingredient\StoreIngredientWithProductsRequest;
use App\Http\Requests\Ingredient\UpdateIngredientRequest;
use App\Http\Requests\Ingredient\UpdateIngredientWithProductsRequest;
use App\Http\Resources\Ingredient\IngredientResource;
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
    public function index(GetIngredientRequest $request)
    {
        $all = Ingredient::get();
        return response()->json($all);
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
    public function show(GetIngredientRequest $request, $id)
    {
        $item = Ingredient::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
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

    public function index_loaded(GetIngredientWithProductsRequest $request)
    {
        $all = Ingredient::with(
            'products', 'type', 'category',
        )->get();
        return response()->json(IngredientResource::collection($all));
    }

    public function show_loaded(GetIngredientRequest $request, $id)
    {
        $item = Ingredient::with('products', 'type', 'category')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json(new IngredientResource($item));
    }

    // создание
    public function store_loaded(StoreIngredientWithProductsRequest $request)
    {
        $item = new Ingredient;
        // сначала создаем ингредиент - потом связи
        $item->name = $request['name'];
        $item->item_weight = $request['item_weight'];
        $item->type_id = $request['type']['id'];

        $category = IngredientCategory::findOrNew($request['category']['id']);
        if(empty($category->id)){
            $category->name = $request['category']['name'];
            $category->save();
        }

        $item->category()->associate($category->id);

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
        $item->item_weight = $request['item_weight'];
        $item->type_id = $request['type']['id'];

        $category = IngredientCategory::findOrNew($request['category']['id']);
        if(empty($category->id)){
            $category->name = $request['category']['name'];
            $category->save();
        }

        $item->category()->associate($category);

        $item->save();

        return response()->json($item, 200);
    }
    
    private function process_products(Ingredient $item, FormRequest $request) {

        $products = [];
        foreach($request->products as $p){
            $product = Product::findOrNew($p['id']);

            if(empty($product->id)){
                $product->name = $p['name'];
                $product->save();
            }

            $products[$product->id] = [
                'waste_percentage'=>$p['waste_percentage'],
                'raw_content_percentage'=>$p['raw_content_percentage']
            ];
        }
    
        $item->products()->sync($products);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteIngredientRequest $request, $id)
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
