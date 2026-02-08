<?php
namespace App\Imports\ProjectImport\SheetImports;

use App\Models\Dish\Dish;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\IngredientIngredient;
use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientPurchaseOption;
use App\Models\Ingredient\IngredientTag;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
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

class IngredientsTagsImport implements ToCollection, WithValidation, WithSkipDuplicates, WithUpserts, SkipsEmptyRows
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
        return ['id', 'tag_id'];
    }
        
    public function rules(): array
    {
        return [
            '0' => 'required|string|max:60',
            '1' => 'required|string|max:60',
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
        $tag = $project->ingredient_tags()->where('name', $row[1])->first();
        
        $ingredient->tags()->attach($tag);
        
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