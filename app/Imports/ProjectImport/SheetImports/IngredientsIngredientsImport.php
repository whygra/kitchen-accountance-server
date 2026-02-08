<?php
namespace App\Imports\ProjectImport\SheetImports;

use App\Models\Ingredient\Ingredient;
use App\Models\Ingredient\IngredientIngredient;
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

class IngredientsIngredientsImport implements ToCollection, WithValidation, WithSkipDuplicates, WithUpserts, SkipsEmptyRows
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
        return ['includer_id', 'included_id'];
    }
        
    public function rules(): array
    {
        return [
            '0' => 'required|string|max:60',
            '1' => 'required|string|max:60',
            '2' => 'required|numeric|min:0.01',
            '3' => 'nullable|numeric|min:0',
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

        $includer = $project->ingredients()->where('name', $row[0])->first();
        $included = $project->ingredients()->where('name', $row[1])->first();

        $amount = $row[2]??0;
        $net_weight = $row[3]??0;
        $waste_percentage = $row[4]??0;
        
        $includer->ingredients()->detach($included);
        $includer->ingredients()->attach(
            $included, [
                'amount'=>$amount, 
                'net_weight' => $net_weight,
                // 'waste_percentage' => 100-($net_weight/$amount/$included->item_weight * 100),
            ]
        );
        
        return $includer;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use($rows){
            foreach ($rows as $row)
                $this->model($row->toArray())->save();
        });
    }
}