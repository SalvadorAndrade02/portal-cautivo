<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $blocked = $this->boolean('blocked');

        $authorized = !$blocked
            && $this->boolean('authorized');

        $bypassPortal = $authorized
            && $this->boolean('bypass_portal');

        $this->merge([
            'portal_user_id' => $this->filled('portal_user_id')
                ? $this->input('portal_user_id')
                : null,

            'last_ip_address' => $this->filled('last_ip_address')
                ? trim((string) $this->input('last_ip_address'))
                : null,

            'mac_address' => $this->normalizeMac(
                $this->input('mac_address')
            ),

            'authorized' => $authorized,
            'blocked' => $blocked,
            'bypass_portal' => $bypassPortal,
        ]);
    }

    public function rules(): array
    {
        return [
            'business_id' => [
                'required',
                'integer',
                Rule::exists('businesses', 'id'),
            ],

            'portal_user_id' => [
                'nullable',
                'integer',
                Rule::exists('portal_users', 'id')
                    ->where(
                        fn(Builder $query) => $query->where(
                            'business_id',
                            $this->input('business_id')
                        )
                    ),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'device_type' => [
                'required',
                Rule::in([
                    'phone',
                    'laptop',
                    'pos',
                    'camera',
                    'printer',
                    'tv',
                    'iot',
                    'other',
                ]),
            ],

            'mac_address' => [
                'required',
                'mac_address',
                'unique:devices,mac_address',
            ],

            'last_ip_address' => [
                'nullable',
                'ip',
            ],

            'authorized' => [
                'required',
                'boolean',
            ],

            'blocked' => [
                'required',
                'boolean',
            ],

            'bypass_portal' => [
                'required',
                'boolean',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'business_id' => 'local',
            'portal_user_id' => 'usuario del portal',
            'name' => 'nombre del dispositivo',
            'device_type' => 'tipo de dispositivo',
            'mac_address' => 'dirección MAC',
            'last_ip_address' => 'última dirección IP',
            'authorized' => 'autorización',
            'blocked' => 'bloqueo',
            'bypass_portal' => 'acceso sin portal',
            'notes' => 'notas',
        ];
    }

    private function normalizeMac(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $clean = preg_replace(
            '/[^0-9A-Fa-f]/',
            '',
            $value
        );

        if ($clean === null || strlen($clean) !== 12) {
            return strtoupper(trim($value));
        }

        return implode(
            ':',
            str_split(strtoupper($clean), 2)
        );
    }
}
