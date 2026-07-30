<?php

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
];
