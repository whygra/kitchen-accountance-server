<?php
namespace App\Http\Resources\User;

use App\Models\User\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'role' => $this->pivot?->role_id ? new RoleResource(Role::with('permissions')
                ->find($this->pivot->role_id)) : null
        ];
    }
}