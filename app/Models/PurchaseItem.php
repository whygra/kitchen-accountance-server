<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'purchase_item_id',
        'purchase_id',
        'amount',
        'price',
    ];

    public function purchaseItem(): HasOne
    {
        return $this->hasOne(PurchaseItem::class);
    }

    public function purchase(): HasOne
    {
        return $this->hasOne(Purchase::class);
    }
}
