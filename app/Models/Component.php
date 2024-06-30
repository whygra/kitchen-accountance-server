<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Component extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'components';
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
        'name',
        'type_id',
    ];

    public function purchaseItem(): HasOne
    {
        return $this->hasOne(ComponentType::class);
    }

    public function dishComponents(): HasMany
    {
        return $this->hasMany(DishComponent::class);
    }

    public function componentProducts(): HasMany
    {
        return $this->hasMany(ComponentProduct::class);
    }
}
