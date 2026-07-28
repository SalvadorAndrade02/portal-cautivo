<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RadiusAuthenticateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => trim(
                (string) $this->input('username')
            ),

            'ip_address' => $this->filled('ip_address')
                ? trim((string) $this->input('ip_address'))
                : null,

            'mac_address' => $this->normalizeMac(
                $this->input('mac_address')
            ),

            'nas_ip_address' => $this->filled(
                'nas_ip_address'
            )
                ? trim(
                    (string) $this->input('nas_ip_address')
                )
                : null,

            'nas_identifier' => $this->filled(
                'nas_identifier'
            )
                ? trim(
                    (string) $this->input('nas_identifier')
                )
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:100',
            ],

            'password' => [
                'required',
                'string',
                'max:255',
            ],

            'ip_address' => [
                'nullable',
                'ip',
            ],

            'mac_address' => [
                'nullable',
                'mac_address',
            ],

            'nas_ip_address' => [
                'nullable',
                'ip',
            ],

            'nas_identifier' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    private function normalizeMac(mixed $value): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
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
