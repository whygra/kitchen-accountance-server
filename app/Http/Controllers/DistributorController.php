<?php

namespace App\Http\Controllers;

use App\Http\Requests\Distributor\DeleteDistributorRequest;
use App\Http\Requests\Distributor\GetDistributorWithPurchaseOptionsRequest;
use App\Models\Distributor\Distributor;
use App\Http\Requests\Distributor\StoreDistributorRequest;
use App\Http\Requests\Distributor\StoreDistributorWithPurchaseOptionsRequest;
use App\Http\Requests\Distributor\UpdateDistributorRequest;
use App\Http\Requests\Distributor\UpdateDistributorWithPurchaseOptionsRequest;
use App\Http\Resources\Distributor\DistributorResource;
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
    public function index(GetDistributorWithPurchaseOptionsRequest $request)
    {
        $all = Distributor::all();
        return response()->json($all);
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
    public function show(GetDistributorWithPurchaseOptionsRequest $request, $id)
    {
        $item = Distributor::find($id);
        return response()->json($item);
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


    public function index_loaded(GetDistributorWithPurchaseOptionsRequest $request)
    {
        $all = Distributor::with('purchase_options.products', 'purchase_options.unit')->get();
        return response()->json(DistributorResource::collection($all));
    }

    public function show_loaded(GetDistributorWithPurchaseOptionsRequest $request, $id)
    {
        $item = Distributor::with('purchase_options.products', 'purchase_options.unit')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "404"
            ], 404);
            
        return response()->json(new DistributorResource($item));
    }

    public function store_loaded(StoreDistributorWithPurchaseOptionsRequest $request)
    {
        
        $item = new Distributor;
        
        // создание записи
        $item->name = $request->name;
        
        $item->save();
        $this->process_purchase_options($request, $item);

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
        
        $this->process_purchase_options($request, $item);

        return response()->json($item);
    }
 
    private function process_purchase_options(FormRequest $request, Distributor $item) {
        $item->purchase_options()->delete();
        foreach($request->purchase_options as $o){

            $option = PurchaseOption::findOrNew($o['id']);
            $option->name = $o['name'] ?? "";
            $option->price = $o['price'];
            $option->net_weight = $o['net_weight'];
            
            $unit = Unit::findOrNew($o['unit']['id']);
            // присвоить значения полей новой записи
            if(empty($unit->id)){
                $unit->long = $o['unit']['long'] ?? "";
                $unit->short = $o['unit']['short'] ?? "";
                $unit->save();
            }
            
            $option->unit()->associate($unit);
            $item->purchase_options()->save($option);

            // создаем/изменяем продукт только если количество продуктов данной позиции не больше 1
            // с коллекцией продуктов работает контроллер позиций закупки
            if($option->products()->count() <= 1){
                $productData = reset($o['products']);
                $product = Product::findOrNew($productData['id']);

                // присвоить значение новой записи
                if(empty($product->id)){
                    $product->name = $productData['name'];
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
    public function destroy(DeleteDistributorRequest $request, $id)
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
