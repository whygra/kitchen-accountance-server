<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    protected $foreignKeys = [
        'type' => 'type_id', 
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ComponentType::class, 'type_id', 'id');
    }

    public function dishes_components(): BelongsToMany
    {
        return $this->belongsToMany(DishComponent::class, 'component_id', 'id');
    }

    public function components_products(): HasMany
    {
        return $this->hasMany(ComponentProduct::class, 'component_id', 'id');
    }

}
