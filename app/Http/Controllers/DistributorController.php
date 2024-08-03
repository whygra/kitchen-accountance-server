<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Http\Requests\StoreDistributorRequest;
use App\Http\Requests\StoreDistributorWithPurchaseOptionsRequest;
use App\Http\Requests\UpdateDistributorRequest;
use App\Http\Requests\UpdateDistributorWithPurchaseOptionsRequest;
use App\Models\Product;
use App\Models\PurchaseOption;
use App\Models\Unit;
use Exception;

class DistributorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = Distributor::all();
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
    public function store(StoreDistributorRequest $request)
    {
        $new = new Distributor;
        $new->name = $request->name;
        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = Distributor::find($id);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Distributor $distributor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDistributorRequest $request, $id)
    {
        $item = Distributor::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        
        $item->save();
        return response()->json($item, 200);
    }


    public function index_loaded()
    {
        $all = Distributor::with('purchase_options.product', 'purchase_options.unit')->get();
        return response()->json($all);
    }

    public function show_loaded($id)
    {
        $item = Distributor::with('purchase_options.product', 'purchase_options.unit')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    public function store_loaded(StoreDistributorWithPurchaseOptionsRequest $request)
    {
        
        $item = new Distributor;
        
        // обновление данных поставщика
        $item->name = $request->name;
        
        // для каждой связи
        foreach ($request->purchase_options as $purchaseOptionData){

            $product_id = $purchaseOptionData['product']['name'];

            // создание, обновление продукта
            switch($purchaseOptionData['product_data_action']){
                case 'create':
                    // создание продукта
                    $product = new Product;
                    $product->name = $purchaseOptionData['product']['name'] ?? '';
                    $product->save();
                    break;
                case 'none':
                    // получение продукта
                    $product = Product::find($product_id);
                    break;
                default:
                    continue 2;
            }

            $unit_id = $purchaseOptionData['unit']['id'];

            // создание, обновление единицы измерения
            switch($purchaseOptionData['unit_data_action']){
                case 'create':
                    // создание единицы измерения
                    $unit = new Unit;
                    $unit->long = $purchaseOptionData['unit']['long'] ?? '';
                    $unit->short = $purchaseOptionData['unit']['short'] ?? '';
                    $unit->save();
                    break;
                case 'none':
                    // получение единицы измерения
                    $unit = Unit::find($unit_id);
                    break;
                default:
                    continue 2;
            }

            // создание связи
            $purchaseOption = new PurchaseOption;
            $purchaseOption->name = $purchaseOptionData['name'];
            $purchaseOption->net_weight = $purchaseOptionData['net_weight'];
            $purchaseOption->price = $purchaseOptionData['price'];
            $purchaseOption->unit()->associate($unit);
            $purchaseOption->product()->associate($product);
            $item->purchase_options()->save($purchaseOption);
        }

        $item->save();

        return response()->json($item->with('purchase_options.product', 'purchase_options.unit'), 201);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update_loaded(UpdateDistributorWithPurchaseOptionsRequest $request, $id)
    {
        $item = Distributor::find($id);
        if (empty($item))
            return response()->json([
                'message' => "Поставщик с id=$id не найден"
            ], 404);

        // для каждой связи поставщик-продукт
        foreach ($request->purchase_options as $purchaseOptionData){

            $purchase_option_id = $purchaseOptionData['id'];            

            // создание, обновление связи
            switch($purchaseOptionData['data_action']){
                case 'create':
                    // создание связи
                    $purchaseOption = new PurchaseOption;
                    $purchaseOption->name = $purchaseOptionData['name'];
                    $purchaseOption->net_weight = $purchaseOptionData['net_weight'];
                    $purchaseOption->price = $purchaseOptionData['price'];
                    break;
                case 'update':
                    // обновление связи
                    $purchaseOption = PurchaseOption::find($purchase_option_id);
                    $purchaseOption->name = $purchaseOptionData['name'];
                    $purchaseOption->net_weight = $purchaseOptionData['net_weight'];
                    $purchaseOption->price = $purchaseOptionData['price'];
                    break;
                case 'none':
                    // получение связи
                    $purchaseOption = PurchaseOption::find($purchase_option_id);
                    break;
                case 'delete':
                    // удаление связи
                    $purchaseOption = PurchaseOption::find($purchase_option_id);
                    $purchaseOption->delete();
                default:
                    continue 2;
            }

            $product_id = $purchaseOptionData['product']['id'];
            // создание, обновление продукта
            switch($purchaseOptionData['product_data_action']){
                case 'create':
                    // создание продукта
                    $product = new Product;
                    $product->name = $purchaseOptionData['product']['name'] ?? '';
                    $product->save();
                    break;
                case 'update':
                    // обновление продукта
                    $product = Product::find($product_id);
                    $product->name = $purchaseOptionData['product']['name'] ?? '';
                    $product->save();
                    break;
                case 'none':
                    // получение продукта
                    $product = Product::find($product_id);
                    break;
                default:
                    continue 2;
            } 

            $unit_id = $purchaseOptionData['unit']['id'];            
            // создание, обновление единицы измерения
            switch($purchaseOptionData['unit_data_action']){
                case 'create':
                    // создание единицы измерения
                    $unit = new Unit;
                    $unit->long = $purchaseOptionData['unit']['long'] ?? '';
                    $unit->short = $purchaseOptionData['unit']['short'] ?? '';
                    $unit->save();
                    break;
                case 'none':
                    // получение единицы измерения
                    $unit = Unit::find($unit_id);
                    break;
                default:
                    continue 2;
            }

            $purchaseOption->unit()->associate($unit);
            $purchaseOption->product()->associate($product);
            $item->purchase_options()->save($purchaseOption);
        }
        // обновление данных поставщика
        $item->name = $request->name;
        $item->save();

        return response()->json($item->with('purchase_options.product', 'purchase_options.unit'), 200);
    }
 
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = Distributor::find($id);
        if(empty($item))
            return response()->json([
                'message' => '404'
            ], 404);
        
        $item->delete();
        return response()->json($item);
    }
}
