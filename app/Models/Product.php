<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends DeletionAllowableModel
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'products';
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
    ];

    public function deletionAllowed() :bool {
        // удаление разрешено, если нет неудаляемых связанных ингредиентов или позиций закупки
        return 
            empty(
                array_filter(
                    $this->purchase_options()->get()->all(), 
                    fn($item)=>!($item->deletionAllowed())
                ))
            &&
            empty(
                array_filter(
                    $this->ingredients_products()->get()->all(), 
                    fn(IngredientProduct $item)=>!($item->ingredient()->get()->first()->deletionAllowed())
            ));
    }

    protected static function booted(): void
    {
        static::deleting(function (Product $product) {
            if (!$this->deletionAllowed())
                return false;
            // удаление связанных записей
            $product->ingredients_products()->delete();
            $product->purchase_options()->delete();
        });
    }

    public function purchase_options(): HasMany
    {
        return $this->hasMany(PurchaseOption::class);
    }

    public function ingredients_products(): HasMany
    {
        return $this->hasMany(IngredientProduct::class);
    }

}
