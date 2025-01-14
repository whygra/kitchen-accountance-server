<?php

namespace App\Models\Ingredient;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IngredientProduct extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'ingredients_products';
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
        'ingredient_id',
        'product_id',
        'raw_product_weight',
        'waste_percentage',
        'updated_by_user_id',
    ];

    protected $foreignKeys = [
        'ingredient' => 'ingredient_id',
        'product' => 'product_id',
        'updated_by_user' => 'updated_by_user_id'
    ];


    protected $casts = [
        'raw_product_weight' => 'float',
        'waste_percentage' => 'float',
   ];
   protected $touches = ['ingredient'];

   
   protected $appends = [
        // 'raw_content_percentage',
    ];

    protected function rawContentPercentage(): Attribute
    {
        return new Attribute(
            get: fn () => 
                $this->raw_product_weight*100/$this->ingredient()->first()->source_weight,
        );
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

}
