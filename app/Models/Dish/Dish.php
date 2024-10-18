<?php

namespace App\Models\Dish;

use App\Models\Ingredient\Ingredient;
use App\Models\MenuItem\MenuItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dish extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'dishes';
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
        'name',
        'image_path',
        'category_id',
    ];

    protected $foreignKeys = [
        'category' => 'category_id',
    ];

    
    // связи M-N с ингредиентами
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'dishes_ingredients')
            ->withPivot('waste_percentage', 'ingredient_amount')
            ->using(DishIngredient::class);
    }

    // позиции меню
    public function menu_items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'dish_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DishCategory::class, 'category_id', 'id');
    }
}