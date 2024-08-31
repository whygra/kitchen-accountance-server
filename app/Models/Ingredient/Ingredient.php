<?php

namespace App\Models\Ingredient;

use App\Models\Dish\Dish;
use App\Models\Dish\DishIngredient;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ingredient extends Model
{
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
        'type_id',
        'category_id',
        'item_weight',
    ];

    protected $foreignKeys = [
        'type' => 'type_id', 
        'category' => 'category_id', 
    ];

    // признак - штучный ингредиент
    protected $appends = [
        'is_item_measured'
    ];

    protected $casts = [
        'item_weight' => 'float'
    ];

    // признак - штучный ингредиент
    protected function isItemMeasured(): Attribute
    {
        return new Attribute(
            get: fn () => $this->item_weight != 1,
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

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'dishes_ingredients')
            ->withPivot(['waste_percentage', 'ingredient_amount'])
            ->using(DishIngredient::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'ingredients_products')
            ->withPivot(['raw_content_percentage', 'waste_percentage'])
            ->using(IngredientProduct::class);
    }

}
