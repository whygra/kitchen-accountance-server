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
    public function store(StoreComponentWithProductsRequest $request)
    {
        
        $item = new Component;
        
        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $item->save();
        
        
        // обновление продуктов
        foreach ($request->products_to_update as $productData){
            $product = Component::find($productData->id);
            $product->name = $productData->name;
            $product->save();
        }
        
        // для каждой связи компонент-продукт
        foreach ($request->component_products as $componentProductData){
            // id продукта текущей связи
            $product_id = $componentProductData["id"];

            // если id продукта == 0, нужно создать продукт
            if($product_id == 0) {
                // создание продукта
                $product = new Product;
                $product->name = $componentProductData["product_name"] ?? "";
                $product->save();
                // записываем в переменную id созданного продукта
                $product_id = $componentProductData["product_id"] = $product->id;
            }

            // создание связи
            $componentProduct = new ComponentProduct;
            // здесь присваиваем связи id продукта
            $componentProduct->product_id = $componentProductData["product_id"] = $product_id;
            $componentProduct->component_id = $item->id;
            $componentProduct->raw_content_percentage = $componentProductData["raw_content_percentage"];
            $componentProduct->waste_percentage = $componentProductData["waste_percentage"];
            $componentProduct->save();
        }

        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = Component::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Component $component)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update(UpdateComponentWithProductsRequest $request, $id)
    {
        $item = Component::find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        // обновление данных компонента
        $item->name = $request->name;
        $item->type_id = $request->type_id;
        $item->save();

        // обновление продуктов
        foreach ($request->products_to_update as $productData){
            $product = Component::find($productData->id);
            $product->name = $productData->name;
            $product->save();
        }

        // для каждой связи компонент-продукт
        foreach ($request->component_products_to_cu as $componentProductData){
            // id продукта текущей связи 
            $product_id = $componentProductData["id"];

            // если id продукта == 0, нужно создать продукт
            if($product_id == 0) {
                // создание продукта
                $product = new Product;
                $product->name = $componentProductData["product_name"];
                $product->save();
                // записываем в переменную id созданного продукта
                $product_id = $componentProductData["product_id"] = $product->id;
            }

            // создание и обновление связей
            // (по соглашению массив component_products_to_cu содержит данные только о связях требующих создания или обновления)

            // если id связи == 0, нужно создать связь
            if($componentProductData["id"] == 0) {
                // создание связи
                $componentProduct = new ComponentProduct;
                // здесь присваиваем связи id продукта
                $componentProduct->product_id = $componentProductData["product_id"] = $product_id;
                $componentProduct->component_id = $item->id;
                $componentProduct->raw_content_percentage = $componentProductData["raw_content_percentage"];
                $componentProduct->waste_percentage = $componentProductData["waste_percentage"];
                $componentProduct->save();
            }
            // иначе - обновить связь
            else{
                // обновление связи
                $componentProduct = ComponentProduct::find($componentProductData["id"]);
                $componentProduct->product_id = $componentProductData["product_id"] = $product_id;
                $componentProduct->component_id = $item->id;
                $componentProduct->raw_content_percentage = $componentProductData["raw_content_percentage"];
                $componentProduct->waste_percentage = $componentProductData["waste_percentage"];
                $componentProduct->save();
            }
        }
         
        // удаление связей
        foreach ($request->component_products_to_delete as $componentProductData){
            $componentProduct = ComponentProduct::find($componentProductData["id"]);
            if (empty($componentProduct))
                continue;
            $componentProduct->delete();
        }

        return response()->json($item, 200);
    }
}
