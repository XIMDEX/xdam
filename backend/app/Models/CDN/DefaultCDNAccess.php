<?php

namespace App\Models\CDN;

class DefaultCDNAccess
{
    public function __construct() {}

    public function areRequirementsMet($ipAddress = null)
    {
        return true;
    }
}