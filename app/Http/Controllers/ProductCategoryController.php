<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductCategory\DeleteProductCategoryRequest;
use App\Http\Requests\ProductCategory\GetProductCategoryRequest;
use App\Http\Requests\ProductCategory\StoreProductCategoryWithProductsRequest;
use App\Http\Requests\ProductCategory\UpdateProductCategoryWithProductsRequest;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductProduct;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetProductCategoryRequest $request)
    {
        $all = ProductCategory::all();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryWithProductsRequest $request)
    {
        $new = new ProductCategory;
        $new->name = $request->name;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetProductCategoryRequest $request, $id)
    {
        $item = ProductCategory::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryWithProductsRequest $request, $id)
    {
        $item = ProductCategory::find($id);
        $item->name = $request->name;
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        $item->name = $request->name;
        $item->save();
            return response()->json($item, 200);
    }

    /**
     * Display the specified resource.
     */

    public function index_loaded(GetProductCategoryRequest $request)
    {
        $all = ProductCategory::with('products')->all();
        return response()->json($all);
    }
    

    public function show_loaded(GetProductCategoryRequest $request, $id)
    {
        $item = ProductCategory::with('products')->find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }
    
    // удаление
    public function destroy(DeleteProductCategoryRequest $request, $id) 
    {
        $item = ProductCategory::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию блюда с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }
}
