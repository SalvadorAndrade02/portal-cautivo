<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'plan_id' => $this->filled('plan_id')
                ? $this->input('plan_id')
                : null,

            'max_devices' => $this->filled('max_devices')
                ? $this->input('max_devices')
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $business = $this->route('business');

        return [
            'plan_id' => [
                'nullable',
                'integer',
                Rule::exists('plans', 'id'),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'local_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('businesses', 'local_number')
                    ->ignore($business),
            ],

            'responsible_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'suspended',
                    'cancelled',
                ]),
            ],

            'max_devices' => [
                'nullable',
                'integer',
                'min:1',
                'max:1000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'plan_id' => 'plan',
            'name' => 'nombre del local',
            'local_number' => 'número de local',
            'responsible_name' => 'responsable',
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
            'address' => 'dirección',
            'status' => 'estado',
            'max_devices' => 'máximo de dispositivos',
        ];
    }
}
