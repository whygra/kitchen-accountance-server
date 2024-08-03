<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOption extends DeletionAllowableModel
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'purchase_options';
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
        'distributor_id',
        'product_id',
        'unit_id',
        'name',
        'net_weight',
        'price',
    ];

    protected $foreignKeys = [
        'product' => 'product_id', 
        'unit' => 'unit_id', 
        'distributor' => 'distributor_id', 
    ];

    protected $casts = [
        'price' => 'float'
    ];

    public function deletionAllowed() :bool {
        // удаление разрешено, если нет связанных заявок
        return empty(
            $this->purchase_items()->get()->all()
        );
    }

    protected static function booted(): void
    {
        static::deleting(function (PurchaseOption $purchaseOption) {
            if (!$purchaseOption->deletionAllowed())
                return false;
            // удаление связанных записей
            $purchaseOption->purchase_items()->delete();
        });
    }

    public function purchase_items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_option_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class, 'distributor_id', 'id');
    }
}
