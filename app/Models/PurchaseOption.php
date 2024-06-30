<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOption extends Model
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
        'product_id',
        'unit_id',
        'name',
        'net_weight',
        'declared_price',
    ];

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseOption::class);
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }

    public function unit(): HasOne
    {
        return $this->hasOne(Unit::class);
    }

    public function distributor(): HasOne
    {
        return $this->hasOne(Distributor::class);
    }
}
