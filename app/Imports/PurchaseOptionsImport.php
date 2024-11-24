<?php

namespace App\Imports;

use App\Models\Distributor\Distributor;
use App\Models\Distributor\PurchaseOption;
use App\Models\Distributor\Unit;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PurchaseOptionsImport implements ToModel, SkipsEmptyRows, WithValidation, SkipsOnFailure, WithUpserts, WithSkipDuplicates
{
    use Importable, SkipsFailures;
    
    private array $columnIndexes = [
        'code' => 0,
        'name' => 1,
        'net_weight' => 3,
        'unit' => 2,
        'price' => 4,
    ];

    private int $distributor_id = 0;

    public function setColumnIndexes(array $columnIndexes){
        $this->columnIndexes = $columnIndexes;
    }

    public function setDistributorId(int $id){
        $this->distributor_id = $id;
    }

    // WithUpserts
    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'name';
    }
        
    public function rules(): array
    {
        return [
            // empty($this->columnIndexes['key']) ? 'key' : $this->columnIndexes['key'] => [
            //     Rule::excludeIf(fn()=>empty($this->columnIndexes['key'])),
            //     'numeric'
            // ],
            $this->columnIndexes['name'] => [
                'string',
                'max:120'
            ],
            // empty($this->columnIndexes['unit']) ? 'unit' : $this->columnIndexes['unit'] => [
            //     Rule::excludeIf(fn()=>empty($this->columnIndexes['unit'])),
            //     'string',
            //     'max:10'
            // ],
            // empty($this->columnIndexes['net_weight']) ? 'net_weight' : $this->columnIndexes['net_weight'] => [
            //     Rule::excludeIf(fn()=>empty($this->columnIndexes['net_weight'])),
            //     'numeric',
            //     'min:1'
            // ],
            $this->columnIndexes['price'] => [
                'numeric',
                'min:0'
            ],
        ];
    }

    public function isEmptyWhen(array $row) : bool {
        return
            empty($row[$this->columnIndexes['name']])
            ||
            empty($row[$this->columnIndexes['price']])
            ;
    }
    
    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
    }

    /**
     * @param array $row
     *
     * @return PurchaseOption|null
     */

    public function model(array $row)
    {
        $distributor = Distributor::find($this->distributor_id);
        $project = Project::find($distributor->project_id);

        $item = $distributor->purchase_options()->where('name', $row[$this->columnIndexes['name']])->firstOrNew();
        if (empty($item->id)){
            
            $item->distributor()->associate($this->distributor_id);
            
            if(!empty($this->columnIndexes['code']))
            $item->code = $row[$this->columnIndexes['code']];
        
            $item->name = $row[$this->columnIndexes['name']];
            
            $unit = empty($this->columnIndexes['unit']) || empty($row[$this->columnIndexes['unit']])
            ? $project->units()->first()
            : $project->units()->where('short', $row[$this->columnIndexes['unit']])->firstOrNew();
            
            if(empty($unit->id)){
                $unit->project_id = $project->id;
                $unit->long = $row[$this->columnIndexes['unit']];
                $unit->short = $row[$this->columnIndexes['unit']];
                $unit->save();
            }
            
            $item->unit()->associate($unit->id);
            $item->net_weight = empty($this->columnIndexes['net_weight'])?1000:$row[$this->columnIndexes['net_weight']];
            $item->price = empty($this->columnIndexes['price'])?0:$row[$this->columnIndexes['price']];
        } else {
            if(!empty($this->columnIndexes['code']))
                $item->code = $row[$this->columnIndexes['code']];
            if(!empty($this->columnIndexes['net_weight']))
                $item->net_weight = $row[$this->columnIndexes['net_weight']];
            if(!empty($this->columnIndexes['price']))
                $item->price = $row[$this->columnIndexes['price']];
        }
        return $item;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use($rows){

            foreach ($rows as $row)
            {
                $this->model($row->toArray())->save();
            }

            $distributor = Distributor::find($this->distributor_id);
            if(Auth::user()->id)
            $distributor->updated_by_user_id = Auth::user()->id;
        });
    }
}