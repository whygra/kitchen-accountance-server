<?php

namespace App\Imports;

use App\Models\Distributor\PurchaseOption;
use App\Models\Distributor\Unit;
use Illuminate\Support\Collection;
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
        $new = new PurchaseOption;
        
        $new->distributor()->associate($this->distributor_id);
        $new->code = empty($this->columnIndexes['code']) ? null : $row[$this->columnIndexes['code']];
        $new->name = $row[$this->columnIndexes['name']];
        $unit = empty($this->columnIndexes['unit']) || empty($row[$this->columnIndexes['unit']])
            ? Unit::find(1) 
            : Unit::where('short', $row[$this->columnIndexes['unit']])->first();
        if(empty($unit->id))
            $unit = Unit::create([
                'long'=>$row[$this->columnIndexes['unit']], 
                'short'=>$row[$this->columnIndexes['unit']],
            ]);
        

        $new->unit()->associate($unit->id);
        $new->net_weight = empty($this->columnIndexes['net_weight'])?1000:$row[$this->columnIndexes['net_weight']];
        $new->price = empty($this->columnIndexes['price'])?0:$row[$this->columnIndexes['price']];
        return $new;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {
            $this->model($row->toArray())->save();
        }
    }
}