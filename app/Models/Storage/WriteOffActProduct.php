<?php

namespace App\Models\Storage;

use App\Models\Product\Product;
use App\Models\Storage\InventoryAct;
use App\Models\Storage\WriteOffAct;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WriteOffActProduct extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'write_off_acts_products';
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
        'write_off_act_id',
        'product_id',
        'amount',
        'net_weight',
    ];

    protected $foreignKeys = [
        'write_off_act' => 'write_off_act_id',
        'product' => 'product_id',
    ];


    protected $casts = [
        'amount' => 'float',
    ];
    protected $touches = ['write_off_act'];

    public function write_off_act(): BelongsTo
    {
        return $this->belongsTo(WriteOffAct::class, 'write_off_act_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

}
