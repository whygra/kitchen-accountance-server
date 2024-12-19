<?php
namespace App\Imports\SheetImports;

use App\Models\Dish\Dish;
use App\Models\Ingredient\Ingredient;
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

class IngredientProductsImport implements ToCollection, WithValidation, WithSkipDuplicates, WithUpserts
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
        return ['ingredient_id', 'product_id'];
    }
        
    public function rules(): array
    {
        return [
            '0' => 'required|string|max:60',
            '1' => 'required|string|max:60',
            '2' => 'required|numeric|min:0.01',
            '3' => 'nullable|numeric|min:0|max:100',
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

        $ingredient = $project->ingredients()->where('name', $row[0])->first();
        $product = $project->products()->where('name', $row[1])->first();
        $content_percentage = $row[2];
        $waste_percentage = $row[3]??0;
        
        $ingredient->products()->save(
            $product, [
                'raw_content_percentage' => $content_percentage, 
                'waste_percentage' => $waste_percentage
            ]
        );
        
        return $ingredient;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use($rows){
            foreach ($rows as $row)
                $this->model($row->toArray())->save();
        });
    }
}