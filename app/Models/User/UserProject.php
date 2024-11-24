<?php

namespace App\Models\User;

use App\Models\Distributor\PurchaseOption;
use App\Models\Product\Product;
use App\Models\Project;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Permission\Traits\HasRoles;

class UserProject extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'users_projects';
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
        'user_id',
        'project_id',
        'role_id',
    ];

    protected $foreignKeys = [
        'user' => 'user_id',
        'project' => 'project_id',
        'role' => 'role_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

}
