<?php
namespace App\Imports\SheetImports;

use App\Models\Dish\Dish;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientType;
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

class IngredientsImport implements ToCollection, WithValidation, WithUpserts, WithSkipDuplicates
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
            '1' => 'required|string|max:2',
            '2' => 'nullable|string|max:60',
            '3' => 'nullable|string|max:60',
            // '4' => 'required|boolean',
            '5' => 'required|numeric|min:0.01',
            '6' => 'nullable|string|max:1000',
        ];
    }

    /**
     * @param array $row
     *
     * @return Ingredient|null
     */

    public function model(array $row)
    {
        $project = Project::find($this->project_id);

        $item = $project->ingredients()->where('name', $row[0])->firstOrNew();
        $type = IngredientType::where('name', $row[1])->first();
        $category = $project->ingredient_categories()->where('name', $row[2]??'')->firstOrNew();
        $group = $project->ingredient_groups()->where('name', $row[3]??'')->firstOrNew();
        $is_item_measured = isset($row[4]);
        $item_weight = $row[5];
        $description = $row[6] ?? '';

        if(empty($category->id)&&!empty($row[2])){
            $category->name = $row[2];
            $project->ingredient_categories()->save($category);
        }
        if(empty($group->id)&&!empty($row[3])){
            $group->name = $row[3];
            $project->ingredient_groups()->save($group);
        }

        if(!empty($category->name))
            $item->category()->associate($category);
        if(!empty($group->name))
            $item->group()->associate($group);

        $item->type()->associate($type->id);
        $item->is_item_measured = $is_item_measured;
        $item->item_weight = $item_weight;
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
                // dd($row);
            }
        });
    }
}