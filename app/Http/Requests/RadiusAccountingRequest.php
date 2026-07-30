<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RadiusAccountingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $statusType = strtolower(
            trim((string) $this->input('status_type'))
        );

        $statusType = str_replace(
            [' ', '_'],
            '-',
            $statusType
        );

        $this->merge([
            'status_type' => $statusType,

            'session_id' => trim(
                (string) $this->input('session_id')
            ),

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

            'termination_reason' => $this->filled(
                'termination_reason'
            )
                ? trim(
                    (string) $this->input('termination_reason')
                )
                : null,

            'called_station_id' => $this->filled(
                'called_station_id'
            )
                ? trim(
                    (string) $this->input('called_station_id')
                )
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'status_type' => [
                'required',
                Rule::in([
                    'start',
                    'interim-update',
                    'stop',
                ]),
            ],

            'session_id' => [
                'required',
                'string',
                'max:191',
            ],

            'username' => [
                'required',
                'string',
                'max:100',
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

            'session_time' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'input_octets' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'output_octets' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'input_gigawords' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'output_gigawords' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'termination_reason' => [
                'nullable',
                'string',
                'max:100',
            ],

            'called_station_id' => [
                'nullable',
                'string',
                'max:191',
            ],

            'nas_port' => [
                'nullable',
                'integer',
                'min:0',
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
