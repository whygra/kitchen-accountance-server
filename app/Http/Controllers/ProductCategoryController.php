<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductCategory\StoreProductCategoryRequest;
use App\Http\Requests\ProductCategory\UpdateProductCategoryRequest;
use App\Models\Product\ProductCategory;
use App\Models\Product\ProductProduct;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = ProductCategory::all();
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
    public function store(StoreProductCategoryRequest $request)
    {
        $new = new ProductCategory;
        $new->name = $request->name;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
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
    public function update(UpdateProductCategoryRequest $request, $id)
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

     public function index_loaded()
     {
         $all = ProductCategory::with('products')->all();
         return response()->json($all);
     }
     

    public function show_loaded($id)
    {
        $item = ProductCategory::with('products')->find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }
    
    // удаление
    public function destroy($id) 
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
