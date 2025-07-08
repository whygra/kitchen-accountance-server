<?php

namespace App\Models\Storage;

use App\Models\Ingredient\Ingredient;
use App\Models\Product\Product;
use App\Models\Storage\InventoryAct;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InventoryActIngredient extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'inventory_acts_ingredients';
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
        'inventory_act_id',
        'ingredient_id',
        'amount',
        'net_weight',
    ];

    protected $foreignKeys = [
        'inventory_act' => 'inventory_act_id',
        'ingredient' => 'ingredient_id',
    ];


    protected $casts = [
        'amount' => 'float',
    ];
    protected $touches = ['inventory_act'];

    public function inventory_act(): BelongsTo
    {
        return $this->belongsTo(InventoryAct::class, 'inventory_act_id', 'id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id', 'id');
    }

}
