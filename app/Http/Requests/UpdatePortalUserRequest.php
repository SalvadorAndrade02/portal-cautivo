<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePortalUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $portalUser = $this->route('portalUser');

        return [
            'business_id' => [
                'required',
                'integer',
                Rule::exists('businesses', 'id'),
            ],

            'username' => [
                'required',
                'string',
                'min:4',
                'max:100',
                'alpha_dash',
                Rule::unique('portal_users', 'username')
                    ->ignore($portalUser),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:100',
                'confirmed',
            ],

            'full_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'suspended',
                    'disabled',
                ]),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'business_id' => 'local',
            'username' => 'nombre de usuario',
            'password' => 'contraseña',
            'full_name' => 'nombre completo',
            'status' => 'estado',
        ];
    }
}
