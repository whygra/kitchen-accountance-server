<?php
namespace App\Imports\ProjectImport\SheetImports;

use App\Models\Dish\Dish;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientType;
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

class IngredientsImport implements ToCollection, WithValidation, WithUpserts, WithSkipDuplicates, SkipsEmptyRows
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
            // '2' => 'required|boolean',
            '3' => 'required|numeric|min:0.01',
            '4' => 'nullable|string|max:1000',
            '6' => 'nullable|numeric|min:0',
            '5' => 'nullable|numeric|min:0',
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
        $is_item_measured = isset($row[2]);
        $item_weight = $row[3];
        $description = $row[4] ?? '';
        $total_gross_weight = $row[5] ?? 0;
        $total_net_weight = $row[6] ?? 0;

        $item->type()->associate($type->id);
        $item->is_item_measured = $is_item_measured;
        $item->item_weight = $item_weight;
        $item->description = $description;
        $item->total_gross_weight = $total_gross_weight;
        $item->total_net_weight = $total_net_weight;

        $item->ingredients()->detach();
        $item->products()->detach();

            
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