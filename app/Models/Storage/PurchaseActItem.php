<?php

namespace App\Models\Storage;

use App\Models\Distributor\PurchaseOption;
use App\Models\Product\Product;
use App\Models\Storage\InventoryAct;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PurchaseActItem extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'purchase_acts_items';
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
        'purchase_act_id',
        'item_id',
        'amount',
        'net_weight',
        'price',
    ];

    protected $foreignKeys = [
        'item' => 'item_id',
        'purchase_act' => 'purchase_act_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'net_weight' => 'float',
        'price' => 'float',
    ];

    protected $touches = ['purchase_act'];

    public function purchase_act(): BelongsTo
    {
        return $this->belongsTo(PurchaseAct::class, 'purchase_act_id', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(PurchaseOption::class, 'item_id', 'id');
    }

}
