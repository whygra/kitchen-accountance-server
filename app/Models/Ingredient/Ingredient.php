<?php

namespace App\Models\Ingredient;

use App\Models\Dish\Dish;
use App\Models\Dish\DishIngredient;
use App\Models\GrossProductArray;
use App\Models\Product\Product;
use App\Models\Project;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class Ingredient extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (Auth::user()) {
                $model->updated_by_user_id = Auth::user()->id;
            }
        });
        static::updating(function ($model) {
            if (Auth::user()) {
                $model->updated_by_user_id = Auth::user()->id;
            }
        });
    }
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'ingredients';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'type_id',
        'is_item_measured',
        'item_weight',
        'updated_by_user_id',
        'total_gross_weight',
        'total_net_weight',
    ];

    protected $foreignKeys = [
        'type' => 'type_id',
        'updated_by_user' => 'updated_by_user_id',
        'project' => 'project_id',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected $casts = [
        'item_weight' => 'float',
        'is_item_measured' => 'boolean',
        'total_gross_weight' => 'float',
        'total_net_weight' => 'float',
        'atr_total_gross_weight' => 'float',
        'atr_total_net_weight' => 'float',
        'avg_waste_percentage' => 'float',
    ];



    protected $appends = [
        // 'atr_total_gross_weight',
        // 'atr_total_net_weight',
        'avg_waste_percentage',
    ];

    public function getAtrTotalGrossWeight()
    {
        return
            array_reduce(
                $this->products()->get()->toArray(),
                fn($total, $p) => $total + $p['pivot']['gross_weight'],
                0
            )
            + array_reduce(
                $this->ingredients()->get()->toArray(),
                fn($total, $p) => $total + $p['pivot']['amount'] * $p['item_weight'],
                0
            );
    }

    public function getAtrTotalNetWeight()
    {
        return
            array_reduce(
                $this->products->toArray(),
                fn($total, $p) => $total + $p['pivot']['net_weight'],
                0
            )
            + array_reduce(
                $this->ingredients->toArray(),
                fn($total, $p) => $total + $p['pivot']['net_weight'],
                0
            );
    }

    protected function avgWastePercentage(): Attribute
    {
        return Attribute::make(
            get: fn() => 100 - ($this->total_net_weight * 100 / ($this->total_gross_weight == 0 ? 1 : $this->total_gross_weight)),
        );
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(IngredientType::class, 'type_id', 'id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(IngredientTag::class, 'ingredients_tags', 'ingredient_id', 'tag_id');
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'dishes_ingredients')
            ->withPivot(['net_weight', 'amount'])
            ->using(DishIngredient::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'ingredients_products')
            ->withPivot(['gross_weight', 'net_weight'])
            ->using(IngredientProduct::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredients_ingredients', 'includer_id', 'included_id')
            ->withPivot(['amount', 'net_weight'])
            ->using(IngredientIngredient::class);
    }

    public function superior_ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredients_ingredients', 'included_id', 'includer_id')
            ->withPivot(['amount', 'net_weight'])
            ->using(IngredientIngredient::class);
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }

    public function getRawProducts(float $weight)
    {
        $gross_products = new GrossProductArray();

        // общая масса брутто составляющих ингредиента
        $gross_weight = $weight * ($this->avg_waste_percentage / 100 + 1);

        foreach ($this->products()->get()->toArray() as $p) {
            $gross_products->addIngredientProduct($p, $gross_weight);
        }
        foreach ($this->ingredients as $i) {
            foreach ($i->getRawProducts($i->pivot->share * $gross_weight) as $id => $p) {
                $gross_products->addProduct($p);
            }
        }
        return $gross_products->get();
    }
}
