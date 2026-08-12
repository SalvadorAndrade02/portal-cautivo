<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInterestAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique(
                    'interest_areas',
                    'name'
                ),
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'redirect_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],

            'active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'redirect_url' => 'URL de redirección',
            'active' => 'estado',
        ];
    }
}
