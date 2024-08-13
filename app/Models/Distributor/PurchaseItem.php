<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseItem extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'purchase_items';
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
        'purchase_option_id',
        'purchase_id',
        'amount',
        'discount',
    ];

    protected $foreignKeys = [
        'purchase_option' => 'purchase_option_id', 
        'purchase' => 'purchase_id', 
    ];

    protected $casts = [
        'discount' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (PurchaseItem $item) {
            // защита от конфликтующих связей
            // не создавать, если id поставщика закупки и позиции закупки не совпадают
            if($item->purchase_option()->get()->distributor_id != $item->purshase()->get()->distributor_id)
                return false;
        });
    }

    public function purchase_option(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
