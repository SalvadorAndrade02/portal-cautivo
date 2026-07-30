<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRadiusApiToken
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $configuredToken = (string) config(
            'services.radius.api_token'
        );

        $providedToken = (string) $request->header(
            'X-Radius-Token'
        );

        if (
            $configuredToken === ''
            || $providedToken === ''
            || !hash_equals(
                $configuredToken,
                $providedToken
            )
        ) {
            return response()->json([
                'authorized' => false,
                'reason' => 'invalid_radius_api_token',
            ], 403);
        }

        return $next($request);
    }
}
