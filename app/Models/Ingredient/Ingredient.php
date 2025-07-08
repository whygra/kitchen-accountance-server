<?php

namespace App\Models\Ingredient;

use App\Models\Dish\Dish;
use App\Models\Dish\DishIngredient;
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

class Ingredient extends Model
{
    use HasFactory;
    
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if(Auth::user())
                $model->updated_by_user_id = Auth::user()->id;
        });
        static::updating(function ($model) {
            if(Auth::user())
                $model->updated_by_user_id = Auth::user()->id;
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
        'category_id',
        'group_id',
        'is_item_measured',
        'item_weight',
        'updated_by_user_id',
    ];

    protected $foreignKeys = [
        'type' => 'type_id', 
        'category' => 'category_id', 
        'group' => 'group_id', 
        'updated_by_user' => 'updated_by_user_id',
        'project' => 'project_id'
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
        'avg_waste_percentage' => 'float',
    ];

    protected $appends = [
        'total_gross_weight',
        'total_net_weight',
        'avg_waste_percentage',
    ];
    
    protected function totalGrossWeight(): Attribute
    {
        return new Attribute(
            get: fn () => array_reduce(
                $this->products()->get()->toArray(),
                fn($total, $p)=>$total+$p['pivot']['gross_weight'],
                0
            ),
        );
    }
    
    protected function totalNetWeight(): Attribute
    {
        return new Attribute(
            get: fn () => array_reduce(
                $this->products()->get()->toArray(),
                fn($total, $p)=>$total+$p['pivot']['net_weight'],
                0
            ),
        );
    }
    
    protected function avgWastePercentage(): Attribute
    {
        return new Attribute(
            get: fn () => 100 - $this->total_net_weight/($this->total_gross_weight!=0?$this->total_gross_weight:1)*100,
        );
    }
    
    public function type(): BelongsTo
    {
        return $this->belongsTo(IngredientType::class, 'type_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IngredientCategory::class, 'category_id', 'id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(IngredientGroup::class, 'group_id', 'id');
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'dishes_ingredients')
            ->withPivot(['net_weight', 'ingredient_amount'])
            ->using(DishIngredient::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'ingredients_products')
            ->withPivot(['gross_weight', 'net_weight'])
            ->using(IngredientProduct::class);
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }

}
