<?php

namespace App\Models\Product;

use App\Models\DeletionAllowableModel;
use App\Models\Distributor\Purchase;
use App\Models\Distributor\PurchaseOption;
use App\Models\Ingredient\IngredientCategory;
use App\Models\Ingredient\IngredientProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    protected $foreignKeys = [
        'category' => 'category_id',
    ];

    public function deletionAllowed() :bool {
        // удаление разрешено, если нет неудаляемых связанных ингредиентов или позиций закупки
        return 
            empty(
                array_filter(
                    $this->purchase_options()->get()->all(),
                    fn(PurchaseOption $item)=>!($item->deletionAllowed())
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
            if (!$product->deletionAllowed())
                return false;
            // удаление связанных записей
            $product->ingredients_products()->delete();
            $product->purchase_options()->detach();
        });
    }

    public function ingredients_products(): HasMany
    {
        return $this->hasMany(IngredientProduct::class);
    }

    public function purchase_options(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOption::class, 'products_purchase_options', 'product_id', 'purchase_option_id')
            ->withPivot('product_share');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IngredientCategory::class, 'category_id', 'id');
    }

}
