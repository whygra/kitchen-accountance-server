<?php
namespace App\Imports\SheetImports;

use App\Models\Dish\Dish;
use App\Models\Dish\DishIngredient;
use App\Models\Ingredient\IngredientProduct;
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

class DishIngredientsImport implements ToCollection, WithValidation, WithSkipDuplicates, WithUpserts
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
        return ['dish_id', 'ingredient_id'];
    }
        
    public function rules(): array
    {
        return [
            '0' => 'required|string|max:60',
            '1' => 'required|string|max:60',
            '2' => 'required|numeric|min:1',
            '3' => 'nullable|numeric|min:0|max:100',
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

        $dish = $project->dishes()->where('name', $row[0])->first();
        $ingredient = $project->ingredients()->where('name', $row[1])->first();
        $ingredient_amount = $row[2];
        $waste_percentage = $row[3]??0;
        
        $dish->ingredients()->save(
            $ingredient, [
                'ingredient_amount'=>$ingredient_amount, 
                'waste_percentage' => $waste_percentage 
            ]
        );
        
        return $dish;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use($rows){
            foreach ($rows as $row)
                $this->model($row->toArray())->save();
        });
    }
}