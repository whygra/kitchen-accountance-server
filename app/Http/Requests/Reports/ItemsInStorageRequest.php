<?php

namespace App\Http\Requests\Reports;

use App\Http\Requests\ChecksPermissionsRequest;
use App\Models\User\PermissionNames;

class ItemsInStorageRequest extends ChecksPermissionsRequest
{
    public function __construct()
    {

        parent::__construct([
            PermissionNames::READ_STORAGE->value,
            PermissionNames::CRUD_STORAGE->value,
        ]);
    }

    public function prepareForValidation()
    {
        parent::prepareForValidation();
        $this->merge(['date' => $this->route('date')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date',
        ];
    }
}
