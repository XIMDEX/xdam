<?php
namespace Lib\Xrole\Contracts;

interface JwtInterface
{
    public function decode($jwtToken): ?array;
}