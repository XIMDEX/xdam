<?php

return [
    'base_url' => env('XEVAL_URL', ''),
    'version' => env('XEVAL_VERSION', ''),
    'sync' => [
        'page_size' => env('XEVAL_SYNC_PS', 30),
        'start_hour' => env('XEVAL_SYNC_START_HOUR', 21),
        'finish_hour' => env('XEVAL_SYNC_FINISH_HOUR', 6)
    ]
];
