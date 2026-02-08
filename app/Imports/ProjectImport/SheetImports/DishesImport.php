<?php

namespace App\Imports\ProjectImport\SheetImports;

use App\Models\Dish\Dish;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class DishesImport implements ToCollection, WithValidation, WithUpserts, WithSkipDuplicates, SkipsEmptyRows
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
            '0' => 'required|string|max:60',
            '1' => 'nullable|string|max:1000',
            '2' => 'nullable|numeric|min:0',
            '3' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * @param array $row
     *
     * @return Dish|null
     */

    public function model(array $row)
    {
        $project = Project::find($this->project_id);

        $item = $project->dishes()->where('name', $row[0])->firstOrNew();
        $description = $row[1] ?? '';

        $item->description = $description;
        $item->total_gross_weight = $row[2] ?? 0;
        $item->total_net_weight = $row[3] ?? 0;

        $item->ingredients()->detach();
        
        if (empty($item->id)){
            $item->name = $row[0];
            $item->project()->associate($project->id);          
        }
        return $item;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use($rows){
            foreach ($rows as $row){     
                $this->model($row->toArray())->save();
            }

        });
        
    }
}