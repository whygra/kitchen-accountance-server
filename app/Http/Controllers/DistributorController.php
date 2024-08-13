<?php

namespace App\Http\Controllers;

use App\Models\Distributor\Distributor;
use App\Http\Requests\Distributor\StoreDistributorRequest;
use App\Http\Requests\Distributor\StoreDistributorWithPurchaseOptionsRequest;
use App\Http\Requests\Distributor\UpdateDistributorRequest;
use App\Http\Requests\Distributor\UpdateDistributorWithPurchaseOptionsRequest;
use App\Models\Product\Product;
use App\Models\Distributor\PurchaseOption;
use App\Models\Distributor\Unit;
use App\Models\Product\ProductPurchaseOption;
use Exception;
use Illuminate\Foundation\Http\FormRequest;

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
        $all = Distributor::with('purchase_options.products', 'purchase_options.unit')->get();
        return response()->json($all);
    }

    public function show_loaded($id)
    {
        $item = Distributor::with('purchase_options.products', 'purchase_options.unit')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json($item);
    }

    public function store_loaded(StoreDistributorWithPurchaseOptionsRequest $request)
    {
        
        $item = new Distributor;
        
        // создание записи
        $item->name = $request->name;
        
        $item->save();
        $this->process_purchase_options($item, $request);

        return response()->json($item);
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

        // обновление данных поставщика
        $item->name = $request->name;
        $item->save();
        
        $this->process_purchase_options($item, $request);

        return response()->json($item);
    }
 
    private function process_purchase_options(Distributor $item, FormRequest $request) {
        $item->purchase_options()->delete();
        foreach($request->purchase_options as $o){

            $option = PurchaseOption::findOrNew($o['id']);
            $option->name = $o['name'];
            $option->price = $o['price'];
            $option->net_weight = $o['net_weight'];
            
            $unit = Unit::find($o['unit']['id']);
            // присвоить значения полей новой записи
            if(empty($unit)){
                $unit = new Unit();
                $unit->long = $o['unit']['long'];
                $unit->short = $o['unit']['short'];
                $unit->save();
            }
            
            $option->unit()->associate($unit);
            $item->purchase_options()->save($option);

            // создаем/изменяем продукт только если количество продуктов данной позиции не больше 1
            // с коллекцией продуктов работает контроллер позиций закупки
            if($option->products()->count() <= 1){
                $product = Product::find($o['product']['id']);

                // присвоить значение новой записи
                if(empty($product)){
                    $product = new Product();
                    $product->name = $o['product']['name'];
                    // id категории - по умолчанию
                    $product->save();
                }
                $option->products()->sync([$product->id => ['product_share'=>100]]);
            }
        }

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
