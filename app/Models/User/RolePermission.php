<?php

namespace App\Models\User;

use App\Models\Ingredient\Ingredient;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermission extends Pivot
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'roles_permissions';
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
        'role_id',
        'permission_id',
    ];

    protected $foreignKeys = [
        'role' => 'role_id', 
        'permission' => 'permission_id', 
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }
}
