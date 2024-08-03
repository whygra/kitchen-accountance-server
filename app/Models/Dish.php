<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dish extends DeletionAllowableModel
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
    ];

    public function deletionAllowed() : bool {
        // удаление разрешено, если нет связанных позиций меню, которые нельзя удалить
        return empty(
            array_filter(
                $this->menu_items()->get()->all(), 
                fn($item)=>!($item->deletionAllowed())
            )
        );
    }

    protected static function booted(): void
    {
        static::deleting(function (Dish $dish) {
            if (!$dish->deletionAllowed())
                return false;
            // удаление связанных записей
            $dish->dishes_ingredients()->delete();
            $dish->menu_items()->delete();
        });
    }

    // связи M-N с ингредиентами
    public function dishes_ingredients(): HasMany
    {
        return $this->hasMany(DishIngredient::class, 'dish_id', 'id');
    }

    // позиции меню
    public function menu_items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'dish_id', 'id');
    }
}