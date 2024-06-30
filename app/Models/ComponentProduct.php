<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ComponentProduct extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'component_products';
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
        'component_id',
        'product_id',
        'raw_content_percentage',
        'waste_percentage',
    ];

    public function component(): HasOne
    {
        return $this->hasOne(Component::class);
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }

}
