<?php

namespace App\Models\Ingredient;

use App\Models\DeletionAllowableModel;
use App\Models\Dish\DishIngredient;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ingredient extends DeletionAllowableModel
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
    ];

    protected $foreignKeys = [
        'type' => 'type_id', 
        'category' => 'category_id', 
    ];

    protected static function booted(): void
    {
        static::deleting(function (Ingredient $ingredient) {
            if (!$ingredient->deletionAllowed())
                return false;
            // удаление связей
            $ingredient->ingredients_products()->delete();
            $ingredient->dishes_ingredients()->delete();
        });
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(IngredientType::class, 'type_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IngredientCategory::class, 'category_id', 'id');
    }

    public function dishes_ingredients(): HasMany
    {
        return $this->hasMany(DishIngredient::class, 'ingredient_id', 'id');
    }

    public function ingredients_products(): HasMany
    {
        return $this->hasMany(IngredientProduct::class, 'ingredient_id', 'id');
    }

    protected function level(): Attribute
    {
        return new Attribute(
            get: fn () => 'primary',
        );
    }

    public function deletionAllowed() :bool {
        // удаление разрешено, если нет неудаляемых связанных блюд
        return 
            empty(
                array_filter(
                    $this->dishes_ingredients()->get()->all(), 
                    fn($item)=>!($item->dish()->get()->first()->deletionAllowed())
                ) 
            );
    }
}
