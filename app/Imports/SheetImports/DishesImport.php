<?php

namespace App\Imports\SheetImports;

use App\Models\Dish\Dish;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class DishesImport implements ToCollection, WithValidation, WithUpserts, WithSkipDuplicates
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
            '1' => 'nullable|string|max:60',
            '2' => 'nullable|string|max:60',
            '3' => 'nullable|string|max:1000',
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
        $category = $project->dish_categories()->where('name', $row[1]??'')->firstOrNew();
        $group = $project->dish_groups()->where('name', $row[2]??'')->firstOrNew();
        $description = $row[3] ?? '';

        if(empty($category->id)&&!empty($row[1])){
            $category->name = $row[1];
            $project->dish_categories()->save($category);
        }
        if(empty($group->id)&&!empty($row[2])){
            $group->name = $row[2];
            $project->dish_groups()->save($group);
        }
        
        if(!empty($category->name))
            $item->category()->associate($category);
        if(!empty($group->name))
            $item->group()->associate($group);

        $item->description = $description;

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