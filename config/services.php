<?php

return [
    'tika' => [
        'url' => env('TIKA_URL', 'http://tika:9998'),
        'timeout' => (int) env('TIKA_TIMEOUT', 60),
    ],
];
