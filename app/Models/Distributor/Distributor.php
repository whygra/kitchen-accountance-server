<?php

namespace App\Models\Distributor;

use App\Models\DeletionAllowableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distributor extends DeletionAllowableModel
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'distributors';
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
    ];

    public function deletionAllowed() : bool {
        // удаление разрешено, если нет связанных закупок
        return empty(
            $this->purchases()->get()->all()
        );
    }

    protected static function booted(): void
    {
        static::deleting(function (Distributor $distributor) {
            if (!$distributor->deletionAllowed())
                return false;
            // удаление связанных записей
            $distributor->purchase_options()->delete();
        });
    }

    public function purchase_options(): HasMany
    {
        return $this->hasMany(PurchaseOption::class);
    }
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
