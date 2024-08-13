<?php

namespace App\Http\Controllers;

use App\Http\Requests\DishCategory\StoreDishCategoryRequest;
use App\Http\Requests\DishCategory\UpdateDishCategoryRequest;
use App\Models\Dish\DishCategory;
use App\Models\Dish\DishIngredient;

class DishCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = DishCategory::all();
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
    public function store(StoreDishCategoryRequest $request)
    {
        $new = new DishCategory;
        $new->name = $request->name;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = DishCategory::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishCategoryRequest $request, $id)
    {
        $item = DishCategory::find($id);
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
         $all = DishCategory::with('dishes')->all();
         return response()->json($all);
     }


    public function show_loaded($id)
    {
        $item = DishCategory::with('dishes')->find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        return response()->json($item);
    }

    // удаление
    public function destroy($id) 
    {
        $item = DishCategory::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти категорию блюда с id=$id"
            ], 404);
            
            
        $item->delete();
        return response()->json($item);
    }
}
