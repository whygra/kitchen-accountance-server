<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComponentWithProductsRequest;
use App\Http\Requests\UpdateComponentWithProductsRequest;
use App\Models\Component;
use App\Models\ComponentProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class ComponentWithProductsController extends Controller
{
    public function index()
    {
        $all = Component::with('components_products.product', 'type')->get();
        return response()->json($all);
    }

    public function show($id)
    {
        $item = Component::with('components_products.product', 'type')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    public function store(StoreComponentWithProductsRequest $request)
    {
        
        $item = new Component;
        
        // для каждой связи компонент-продукт
        foreach ($request->components_products as $componentProductData){

            $product_id = $componentProductData['product_id'];

            // создание, обновление продукта
            switch($componentProductData['product_data_action']){
                case 'create':
                    // создание продукта
                    $product = new Product;
                    $product->name = $componentProductData['product_name'] ?? '';
                    $product->save();
                    break;
                case 'update':
                    // обновление продукта
                    $product = Product::find($product_id);
                    $product->name = $componentProductData['product_name'] ?? '';
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
            $componentProduct = new ComponentProduct;
            $componentProduct->raw_content_percentage = $componentProductData['raw_content_percentage'];
            $componentProduct->waste_percentage = $componentProductData['waste_percentage'];
            $item->components_products()->save($componentProduct->product()->associate($product));
                    
        }
        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $item->save();
        
        return response()->json($item, 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateComponentWithProductsRequest $request, $id)
    {
        $item = Component::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Компонент с id=$id не найден"
            ], 404);

        // для каждой связи компонент-продукт
        foreach ($request->components_products as $componentProductData){

            $product_id = $componentProductData['product_id'];
            $component_product_id = $componentProductData['id'];            

            // создание, обновление связи
            switch($componentProductData['data_action']){
                case 'create':
                    // создание связи
                    $componentProduct = new ComponentProduct;
                    $componentProduct->waste_percentage = $componentProductData['waste_percentage'];
                    $componentProduct->raw_content_percentage = $componentProductData['raw_content_percentage'];
                    break;
                case 'update':
                    // обновление связи
                    $componentProduct = ComponentProduct::find($component_product_id);
                    $componentProduct->waste_percentage = $componentProductData['waste_percentage'];
                    $componentProduct->raw_content_percentage = $componentProductData['raw_content_percentage'];
                    break;
                case 'none':
                    // получение связи
                    $componentProduct = ComponentProduct::find($component_product_id);
                    break;
                case 'delete':
                    // удаление связи
                    $componentProduct = ComponentProduct::find($component_product_id);
                    $componentProduct->delete();
                default:
                    continue 2;
            }

            // создание, обновление продукта
            switch($componentProductData['product_data_action']){
                case 'create':
                    // создание продукта
                    $product = new Product;
                    $product->name = $componentProductData['product_name'] ?? '';
                    $product->save();
                    break;
                case 'update':
                    // обновление продукта
                    $product = Product::find($product_id);
                    $product->name = $componentProductData['product_name'] ?? '';
                    $product->save();
                    break;
                case 'none':
                    // получение продукта
                    $product = Product::find($product_id);
                    break;
                default:
                    continue 2;
            } 

            $item->components_products()->save($componentProduct->product()->associate($product));
        }
        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $item->save();

        return response()->json($item, 200);
    }
}
