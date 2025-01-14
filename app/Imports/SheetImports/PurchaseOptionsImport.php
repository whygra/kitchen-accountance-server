<?php
namespace App\Imports\SheetImports;

use App\Models\Dish\Dish;
use App\Models\Distributor\PurchaseOption;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class PurchaseOptionsImport implements ToCollection, WithValidation, WithUpserts, WithSkipDuplicates
{
    use Importable;

    private int $project_id = 0;

    public function __construct(int $project_id) {
        $this->project_id = $project_id;
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
            '0' => 'nullable',
            '1' => 'required|string|max:120',
            '2' => 'required|numeric|min:1',
            '3' => 'required|numeric|min:1',
            '4' => 'required|string|max:60',
            '5' => 'required|string|max:12',
        ];
    }

    /**
     * @param array $row
     *
     * @return PurchaseOption|null
     */

    public function model(array $row)
    {
        $project = Project::find($this->project_id);

        $item = PurchaseOption::whereHas(
            'distributor', 
            function (Builder $query) use($project) {
                $query->where('project_id', $project->id);
            }
        )->where('name', $row[1])->firstOrNew();
        $code = $row[0];
        $net_weight = $row[2];
        $price = $row[3];
        $distributor = $project->distributors()->where('name', $row[4])->firstOrNew();
        $unit = $project->units()->orWhere('long', $row[5])->orWhere('short', $row[5])->firstOrNew();

        if(empty($distributor->id)){
            $distributor->name = $row[4];
            $project->distributors()->save($distributor);
        }
        if(empty($unit->id)){
            $unit->long = $unit->short = $row[5];
            $project->units()->save($unit);
        }

        $item->code = $code;
        $item->net_weight = $net_weight;
        $item->price = $price;
        $item->unit()->associate($unit);
        if (empty($item->id)){
            $item->name = $row[1];
            $item->distributor()->associate($distributor);
        } 
        return $item;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use($rows){
            foreach ($rows as $row)
                $this->model($row->toArray())->save();
        });
    }
}