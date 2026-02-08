<?php

namespace App\Models\Ingredient;

use App\Models\Ingredient\Ingredient;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IngredientIngredient extends Pivot
{
    use HasFactory;
    
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'ingredients_ingredients';
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
        'includer_id',
        'included_id',
        'net_weight',
        'amount',
    ];

    protected $foreignKeys = [
        'includer' => 'includer_id', 
        'included' => 'included_id', 
    ];

    protected $casts = [
        'amount' => 'float',
        'net_weight' => 'float',
   ];

   protected $touches = ['includer'];

   protected $appends = [
        'share'
   ];

    protected function share(): Attribute
    {
        return new Attribute(
            get: fn () => $this->amount*$this->included->item_weight/($this->includer->total_gross_weight==0?1:$this->includer->total_gross_weight),
        );
    }

    public function includer(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'includer_id', 'id');
    }

    public function included(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'included_id', 'id');
    }
}
