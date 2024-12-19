<?php

namespace App\Models\Distributor;

use App\Models\Project;
use App\Models\User\SubscriptionPlan;
use App\Models\User\SubscriptionPlanNames;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Distributor extends Model
{
    use HasFactory;

    
      
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if(Auth::user()){
                $model->updated_by_user_id = Auth::user()->id;
            }
        });
        static::updating(function ($model) {
            if(Auth::user()){
                $model->updated_by_user_id = Auth::user()->id;
            }
        });
    }
    
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
        'updated_by_user_id',
        'updated_at',
    ];

    protected $foreignKeys = [
        'updated_by_user' => 'updated_by_user_id',
        'project' => 'project_id',
    ];

    public function getSubscriptionPlan(): mixed {
        return $this->project()->first()->getSubscriptionPlan() 
            ?? SubscriptionPlan::where('name', SubscriptionPlanNames::NONE)->first();
    }

    public function freePurchaseOptionSlots() : int {
        return $this->getSubscriptionPlan()['max_num_purchase_options'] - $this->purchase_options()->count();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function purchase_options(): HasMany
    {
        return $this->hasMany(PurchaseOption::class);
    }
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function updated_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }
}
