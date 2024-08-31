<?php

namespace App\Models\Dish;

use App\Models\Ingredient\Ingredient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DishIngredient extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'dishes_ingredients';
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dish_id',
        'ingredient_id',
        'waste_percentage',
        'ingredient_amount',
    ];

    protected $foreignKeys = [
        'dish' => 'dish_id', 
        'ingredient' => 'ingredient_id', 
    ];

    protected $casts = [
        'ingredient_amount' => 'float',
        'waste_percentage' => 'float',
   ];

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'dish_id', 'id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id', 'id');
    }
}
