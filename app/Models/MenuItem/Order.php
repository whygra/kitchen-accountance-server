<?php

namespace App\Models\MenuItem;

use App\Models\Ingredient\Ingredient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'orders';
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
        'is_paid',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Ingredient $ingredient) {
            $ingredient->menu_items_orders()->delete();
        });
    }

    public function menu_items(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_items_orders')
            ->withPivot('amount', 'discount')
            ->using(MenuItemOrder::class);
    }
}
