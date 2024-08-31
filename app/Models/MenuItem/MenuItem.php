<?php

namespace App\Models\MenuItem;

use App\Models\Dish\Dish;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MenuItem extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'menu_items';
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
        'dish_id',
        'price',
    ];

    protected $foreignKeys = [
        'dish' => 'dish_id', 
    ];

    public function deletionAllowed() :bool {
        // удаление разрешено, если нет связей с заказами
        return empty(
            $this->menu_items_orders()->get()->all()
        );
    }

    public function dish(): BelongsTo 
    {
        return $this->belongsTo(Dish::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'menu_items_orders')
            ->withPivot(['amount', 'discount'])
            ->using(MenuItemOrder::class);
    }
}
