<?php
namespace App\Imports\SheetImports;

use App\Models\Dish\Dish;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\IngredientProduct;
use App\Models\Product\Product;
use App\Models\Product\ProductPurchaseOption;
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

class ProductPurchaseOptionsImport implements ToCollection, WithValidation, WithSkipDuplicates, WithUpserts
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
        return ['product_id', 'purchase_option_id'];
    }
        
    public function rules(): array
    {
        return [
            '0' => 'required|string|max:60',
            '1' => 'required|string|max:120',
            '2' => 'required|numeric|min:0.01|max:100',
        ];
    }

    /**
     * @param array $row
     *
     * @return Product|null
     */

    public function model(array $row)
    {
        $project = Project::find($this->project_id);

        $product = $project->products()->where('name', $row[0])->first();
        $purchase_option = PurchaseOption::whereHas(
            'distributor', 
            function (Builder $query) use($project) {
                $query->where('project_id', $project->id);
            }
        )->where('name', $row[1])->first();
        $product_share = $row[2];
        
        $product->purchase_options()->detach($purchase_option);
        $product->purchase_options()->attach(
            $purchase_option->id, [
                'product_share' => $product_share, 
            ]
        );
        
        return $product;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use($rows){
            foreach ($rows as $row)
                $this->model($row->toArray())->save();
        });
    }
}