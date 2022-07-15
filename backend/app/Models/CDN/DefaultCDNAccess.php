<?php

namespace App\Models\CDN;

class DefaultCDNAccess
{
    public function __construct($parameters) 
    {
        $this->parameters = json_decode($parameters);
    }

    public function areRequirementsMet($ipAddress = null)
    {
        return true;
    }
}