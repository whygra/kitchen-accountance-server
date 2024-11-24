<?php
namespace App\Http\Resources\Project;

use App\Models\User\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RoleResource extends JsonResource
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
            'logo' => [
                'url'=>$this->logo_name == '' ? '' 
                    : url()->to('/')
                    .Storage::url(
                        'images/project_'
                        .$this->project_id
                        .'/logo/'
                        .$this->logo_name
                    ),
                'name'=>$this->logo_name,
            ],
            'backdrop' => [
                'url'=>$this->backdrop_name == '' ? '' 
                    : url()->to('/')
                    .Storage::url(
                        'images/project_'
                        .$this->project_id
                        .'/backdrop/'
                        .$this->backdrop_name
                    ),
                'name'=>$this->backdrop_name,
            ],
            'role' => Role::with('permissions')->find($this->role_id)
            
        ];
    }
}