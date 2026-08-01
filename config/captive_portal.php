<?php

$allowedOrigins = array_values(
    array_filter(
        array_map(
            static fn(string $origin): string =>
            rtrim(trim($origin), '/'),

            explode(
                ',',
                (string) env(
                    'CAPTIVE_PORTAL_ALLOWED_ORIGINS',
                    ''
                )
            )
        )
    )
);

return [
    'visitor_access_ttl_hours' => (int) env(
        'VISITOR_ACCESS_TTL_HOURS',
        8
    ),

    'privacy_notice_version' => env(
        'PRIVACY_NOTICE_VERSION',
        '1.0'
    ),

    'terms_version' => env(
        'TERMS_VERSION',
        '1.0'
    ),

    /*
     * Solamente estos portales podrán recibir las
     * credenciales temporales desde Laravel.
     */
    'allowed_origins' => $allowedOrigins,

    'default_origin' => rtrim(
        (string) env(
            'CAPTIVE_PORTAL_DEFAULT_ORIGIN',
            ''
        ),
        '/'
    ),

    'post_login_redirect_url' => env(
        'CAPTIVE_PORTAL_POST_LOGIN_URL',
        'http://neverssl.com'
    ),

    'visitor_session_stale_minutes' => (int) env(
        'VISITOR_SESSION_STALE_MINUTES',
        15
    ),
];
