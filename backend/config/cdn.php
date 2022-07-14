<?php

return [
    'cdns' => [
        '1' => [
            'collections' => [1, 2, 3, 4],
            'access' => 'DEFAULT'
        ],
        '2' => [
            'collections' => [1, 4],
            'access' => 'IP ADDRESS',
            'ip_addresses' => ['192.168.1.80', '192.168.1.89', '192.168.1.90']
        ]
    ]
];