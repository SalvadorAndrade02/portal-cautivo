<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class TurnstileVerifier
{
    public function verify(
        string $token,
        ?string $remoteIp = null
    ): bool {
        $secretKey = (string) config(
            'services.turnstile.secret_key'
        );

        $verifyUrl = (string) config(
            'services.turnstile.verify_url'
        );

        if ($secretKey === '' || $verifyUrl === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(8)
                ->retry(2, 200)
                ->post(
                    $verifyUrl,
                    array_filter([
                        'secret' => $secretKey,
                        'response' => $token,
                        'remoteip' => $remoteIp,
                        'idempotency_key' => (string) Str::uuid(),
                    ])
                );

            return $response->successful()
                && $response->json('success') === true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
