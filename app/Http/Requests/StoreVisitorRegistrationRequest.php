<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitorRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = preg_replace(
            '/[^0-9]/',
            '',
            (string) $this->input('phone')
        );

        $interestAreaIds = collect(
            $this->input('interest_area_ids', [])
        )
            ->filter()
            ->map(fn(mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->merge([
            'full_name' => trim(
                (string) $this->input('full_name')
            ),

            'phone' => $phone,

            'email' => strtolower(
                trim((string) $this->input('email'))
            ),

            'interest_area_ids' => $interestAreaIds,

            'marketing_consent' => $this->boolean(
                'marketing_consent'
            ),

            'mac_address' => $this->normalizeMac(
                $this->input('mac_address')
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10,15}$/',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
            ],

            'interest_area_ids' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],

            'interest_area_ids.*' => [
                'required',
                'integer',
                'distinct',

                Rule::exists('interest_areas', 'id')
                    ->where(
                        fn(Builder $query) => $query->where(
                            'active',
                            true
                        )
                    ),
            ],

            'privacy_accepted' => [
                'accepted',
            ],

            'terms_accepted' => [
                'accepted',
            ],

            'marketing_consent' => [
                'boolean',
            ],

            'cf-turnstile-response' => [
                'required',
                'string',
                'max:2048',
            ],

            /*
             * Honeypot: una persona no debe llenar este campo.
             */
            'website' => [
                'nullable',
                'string',
                'max:0',
            ],

            /*
             * Posteriormente será entregada por OPNsense.
             * El navegador no puede obtener la MAC directamente.
             */
            'mac_address' => [
                'nullable',
                'mac_address',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' =>
            'El teléfono debe contener entre 10 y 15 dígitos.',

            'interest_area_ids.required' =>
            'Selecciona por lo menos un área de interés.',

            'interest_area_ids.min' =>
            'Selecciona por lo menos un área de interés.',

            'privacy_accepted.accepted' =>
            'Debes aceptar el aviso de privacidad.',

            'terms_accepted.accepted' =>
            'Debes aceptar los términos de uso.',

            'cf-turnstile-response.required' =>
            'Completa la verificación de seguridad.',

            'website.max' =>
            'No fue posible procesar el registro.',
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
