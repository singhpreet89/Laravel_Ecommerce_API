<?php

return [
    'CACHE_TTL' => env('CACHE_TTL', 86400),             // 1 day in seconds
    'APP_IV'    => env('APP_IV', '4bf468c5892e228d'),   // 16 bytes for AES-256-GCM         
    'ENCRYPT'   => 'encrypt',
    'DECRYPT'   => 'decrypt',
];