<?php

namespace App\Models\Distributor;

use App\Models\DeletionAllowableModel;
use App\Models\Product\Product;
use App\Models\Product\ProductPurchaseOption;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'unit_id',
        'name',
        'net_weight',
        'price',
    ];

    protected $foreignKeys = [
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
            $purchaseOption->products()->detach();
            $purchaseOption->purchase_items()->delete();
        });
    }

    public function purchase_items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_option_id', 'id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'products_purchase_options', 'purchase_option_id', 'product_id')
            ->withPivot('product_share');
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
