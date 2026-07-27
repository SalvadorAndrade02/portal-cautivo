<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->boolean('active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:plans,name',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'download_speed_mbps' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
            'upload_speed_mbps' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
            'session_timeout_minutes' => [
                'required',
                'integer',
                'min:1',
                'max:10080',
            ],
            'idle_timeout_minutes' => [
                'required',
                'integer',
                'min:1',
                'max:1440',
            ],
            'max_devices' => [
                'required',
                'integer',
                'min:1',
                'max:1000',
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
            'download_speed_mbps' => 'velocidad de descarga',
            'upload_speed_mbps' => 'velocidad de subida',
            'session_timeout_minutes' => 'duración máxima de sesión',
            'idle_timeout_minutes' => 'tiempo de inactividad',
            'max_devices' => 'máximo de dispositivos',
            'active' => 'estado',
        ];
    }
}
