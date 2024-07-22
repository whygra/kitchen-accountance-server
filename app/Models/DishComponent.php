<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DishComponent extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'dishes_components';
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
        'component_id',
        'waste_percentage',
        'component_raw_weight',
    ];

    protected $foreignKeys = [
        'dish' => 'dish_id', 
        'component' => 'component_id', 
    ];

    protected $casts = [
        'component_raw_weight' => 'float',
        'waste_percentage' => 'float',
   ];

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'dish_id', 'id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'component_id', 'id');
    }
}
