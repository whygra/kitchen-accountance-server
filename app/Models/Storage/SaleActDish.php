<?php

namespace App\Models\Storage;

use App\Models\Dish\Dish;
use App\Models\Storage\InventoryAct;
use App\Models\Storage\SaleAct;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SaleActDish extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'sale_acts_items';
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
        'sale_act_id',
        'dish_id',
        'amount',
        'price',
    ];

    protected $foreignKeys = [
        'sale_act' => 'sale_act_id',
        'dish' => 'dish_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'price' => 'float',
    ];

    protected $touches = ['sale_act'];

    public function sale_act(): BelongsTo
    {
        return $this->belongsTo(SaleAct::class, 'sale_act_id', 'id');
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'dish_id', 'id');
    }

}
