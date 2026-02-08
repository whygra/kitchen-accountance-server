<?php
namespace App\Imports\ProjectImport\SheetImports;

use App\Models\Dish\Dish;
use App\Models\Dish\DishIngredient;
use App\Models\Ingredient\IngredientProduct;
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

class DishesIngredientsImport implements ToCollection, WithValidation, WithSkipDuplicates, WithUpserts, SkipsEmptyRows
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
            '2' => 'nullable|numeric|min:0.01',
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

        $dish = $project->dishes()->where('name', $row[0])->first();
        $ingredient = $project->ingredients()->where('name', $row[1])->first();
        $amount = $row[2]??0;
        $net_weight = $row[3]??0;
        
        $dish->ingredients()->detach($ingredient);
        $dish->ingredients()->attach(
            $ingredient, [
                'amount'=>$amount, 
                'net_weight' => $net_weight,
                // 'waste_percentage' => 100 - $net_weight/($ingredient->item_weight!=0?$ingredient->item_weight*$amount:1)*100,
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