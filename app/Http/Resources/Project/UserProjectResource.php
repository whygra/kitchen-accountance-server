<?php
namespace App\Http\Resources\Project;

use App\Http\Resources\User\RoleResource;
use App\Models\User\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserProjectResource extends JsonResource
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
            'creator' => $this->creator()->first(),
            'updated_by_user' => $this->updated_by_user()->first(),
            'updated_at' => $this->updated_at,
            'logo' => [
                'url'=>$this->logo_name == '' ? '' 
                    : url()->to('/').Storage::url(
                        $this->getLogoDirectoryPath().'/'
                        .$this->logo_name
                    ),
                'name'=>$this->logo_name,
            ],
            'backdrop' => [
                'url'=>$this->backdrop_name == '' ? '' 
                    : url()->to('/')
                    .Storage::url(
                        $this->getBackdropDirectoryPath().'/'
                        .$this->backdrop_name
                    ),
                'name'=>$this->backdrop_name,
            ],
            'is_public' => $this->is_public,
            'role' => new RoleResource(Role::with('permissions')->find($this->pivot->role_id))
        ];
    }
}