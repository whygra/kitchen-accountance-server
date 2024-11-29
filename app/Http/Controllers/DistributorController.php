<?php

namespace App\Http\Controllers;

use App\Http\Requests\Distributor\DeleteDistributorRequest;
use App\Http\Requests\Distributor\GetDistributorWithPurchaseOptionsRequest;
use App\Http\Requests\Distributor\UploadPurchaseOptionsFileRequest;
use App\Models\Distributor\Distributor;
use App\Http\Requests\Distributor\StoreDistributorRequest;
use App\Http\Requests\Distributor\StoreDistributorWithPurchaseOptionsRequest;
use App\Http\Requests\Distributor\UpdateDistributorRequest;
use App\Http\Requests\Distributor\UpdateDistributorWithPurchaseOptionsRequest;
use App\Http\Resources\Distributor\DistributorResource;
use App\Imports\PurchaseOptionsImport;
use App\Models\Product\Product;
use App\Models\Distributor\PurchaseOption;
use App\Models\Distributor\Unit;
use App\Models\Product\ProductPurchaseOption;
use App\Models\Project;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DistributorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetDistributorWithPurchaseOptionsRequest $request, $project_id)
    {
        $all = Project::find($project_id)->distributors()->get();
        return response()->json($all);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDistributorRequest $request, $project_id)
    {
        $new = new Distributor;
        $new->name = $request->name;
        Project::find($project_id)->distributors()->save($new);
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetDistributorWithPurchaseOptionsRequest $request, $project_id, $id)
    {
        $item = Project::find($project_id)->distributors()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти поставщика с id=".$item->id
            ], 404);
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDistributorRequest $request, $project_id, $id)
    {
        $item = Project::find($project_id)->distributors()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти поставщика с id=".$item->id
            ], 404);
        $item->name = $request['name'];
        $item->save();
        return response()->json($item, 200);
    }


    public function index_loaded(GetDistributorWithPurchaseOptionsRequest $request, $project_id)
    {
        $all = Project::find($project_id)->distributors()
            ->with('purchase_options.products', 'purchase_options.unit')->get();
        return response()->json(DistributorResource::collection($all));
    }

    public function show_loaded(GetDistributorWithPurchaseOptionsRequest $request, $project_id, $id)
    {
        $item = Project::find($project_id)->distributors()
            ->with('purchase_options.products', 'purchase_options.unit')->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Не удалось найти поставщика с id=".$item->id
            ], 404);
        return response()->json(new DistributorResource($item));
    }

    public function store_loaded(StoreDistributorWithPurchaseOptionsRequest $request, $project_id)
    {
        $item = new Distributor;
        $project = Project::find($project_id);
        DB::transaction(function() use ($request, $project, $item) {
            // создание записи
            $item->name = $request->name;
            $project->distributors()->save($item);
            $this->process_purchase_options($request, $item);
        });
        
        return response()->json($item);
    }

    /**
     * Update the specified resource in storage.
     */ 
    public function update_loaded(UpdateDistributorWithPurchaseOptionsRequest $request, $project_id, $id)
    {
        $project = Project::find($project_id);
        $item = $project->distributors()->find($id);
        if (empty($item))
            return response()->json([
                'message' => "Поставщик с id=$id не найден"
            ], 404);

        DB::transaction(function() use ($request, $project, $item) {
            // обновление данных поставщика
            $item->name = $request->name;
            
            $this->process_purchase_options($request, $item);
            $project->distributors()->save($item);
        });
        return response()->json($item);

    }
    public function upload_options_file(UploadPurchaseOptionsFileRequest $request, $project_id, $id)
    {
        $item = Distributor::where('project_id', $request['project_id'])->find($request->id);
        if (empty($item))
            return response()->json([
                'message' => "Поставщик с id=$request->id не найден"
            ], 404);

        $path = $request->file('file')->store('distributor_'.$item->id.'_purchase_options');
        
        $import = new PurchaseOptionsImport;
        // throw new HttpResponseException(response()->json([
        //     'success'   => false,
        //     'message'   => json_encode($request['column_indexes']),
        // ], 400));

        $import->setColumnIndexes($request['column_indexes']);
        $import->setDistributorId($item->id);

        Excel::import($import, $path);

        $failures = $import->failures();

        // удалить файл
        Storage::delete($path);
        
        return response()->json([
            'item'=>$item,
            // 'imported_rows'=>$purchase_options,
            'failures'=>$failures,
        ]);
    }
 
    private function process_purchase_options(FormRequest $request, Distributor $item) {

        $requestIds = array_map(fn($o)=>$o['id'], $request->purchase_options);
        $ids = $item->purchase_options()->get(['id'])->toArray();
        $ids = array_map(fn($id)=>$id['id'], $ids);
        $idsToDelete = array_filter($ids, fn($id)=>!in_array($id, $requestIds));

        $project = Project::find($request->project_id);
        
        foreach($idsToDelete as $id)
            $item->purchase_options()->find($id)?->delete();
        
        foreach($request->purchase_options as $o){
            
            $option = Distributor::find($item->id)->purchase_options()->findOrNew($o['id']);
            $option->name = $o['name'];
            $option->price = $o['price'];
            $option->net_weight = $o['net_weight'];
            $option->code = $o['code'] ?? null;
            
            $unit = $project->units()->findOrNew($o['unit']['id']);
            // присвоить значения полей новой записи
            if(empty($unit->id)){
                $unit->long = $o['unit']['long'];
                $unit->short = $o['unit']['short'];
                $project->units()->save($unit);
            }
            
            $option->unit()->associate($unit);

            if($option->isDirty()){
                $item->purchase_options()->save($option);
                $item->touch();
            }

            // создаем/изменяем продукт только если количество продуктов данной позиции не больше 1
            //     с коллекцией продуктов работает контроллер позиций закупки
            // и коллекция данных продуктов на пуста
            if($option->products()->count() <= 1 && !empty($o['products'])){
                $productData = reset($o['products']);
                $product = $project->products()->findOrNew($productData['id']);

                // присвоить значение новой записи
                if(empty($product->id)){
                    $product->name = $productData['name'];
                    // id категории - по умолчанию
                    $project->products()->save($product);
                }
                
                $option->products()->sync([$product->id => ['product_share'=>100]]);
            }
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteDistributorRequest $request, $project_id, $id)
    {
        $item = Project::find($project_id)->distributors()->find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти поставщика с id=".$item->id
            ], 404);
        
        $item->delete();
        return response()->json($item);
    }
}
