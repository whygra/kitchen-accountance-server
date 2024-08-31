<?php

namespace App\Models\MenuItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MenuItemOrder extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'menu_items_orders';
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
        'order_id',
        'menu_item_id',
        'amount',
        'discount',
    ];

    protected $foreignKeys = [
        'dish' => 'dish_id', 
        'menu_item' => 'menu_item_id', 
    ];

    protected $casts = [
        'discount' => 'float',
    ];

    public function dish(): BelongsTo 
    {
        return $this->belongsTo(Order::class);
    }

    public function menu_item(): BelongsTo 
    {
        return $this->belongsTo(MenuItem::class);
    }
}
