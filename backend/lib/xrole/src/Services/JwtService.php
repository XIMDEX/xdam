<?php

namespace Lib\Xrole\Services;

use Lib\Xrole\Contracts\JwtInterface;

class JwtService
{
    private $jwt;

    public function __construct(JwtInterface $jwt)
    {
        $this->jwt = $jwt;

    }

    public function verifyToken(string $jwtToken): ?array
    {
        return $this->jwt->decode($jwtToken);
    }
}
