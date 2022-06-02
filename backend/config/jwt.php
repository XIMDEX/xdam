<?php

return [
    'keys' => [
        // 'public' => 'sdeadbeefcafef00dbabec001e57b00b5ecret',
        'public' => 'file://' . storage_path('jwt/public.pem'),
        'private' => 'file://' . storage_path('jwt/private.pem')
    ],
    'algo' => 'RS256',
    'required_claims' => ["iat", "exp", "id", "ev", "fn", "ln", "os", "gs", "ors", "roles", "username",],
];