<?php

namespace App\Models\Product;

use App\Models\Distributor\PurchaseOption;
use App\Models\Product\Product;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductPurchaseOption extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'products_purchase_options';
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
        'purchase_option_id',
        'product_share',
    ];

    protected $foreignKeys = [
        'purchase_option' => 'purchase_option_id',
        'product' => 'product_id',
        'updated_by_user' => 'updated_by_user_id'
    ];

    protected $casts = [
        'product_share' => 'float',
   ];
   protected $touches = ['product'];

    public function purchase_option(): BelongsTo
    {
        return $this->belongsTo(PurchaseOption::class, 'purchase_option_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }

}
