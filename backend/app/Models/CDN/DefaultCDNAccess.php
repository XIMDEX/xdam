<?php

namespace App\Models\CDN;

class DefaultCDNAccess
{
    public function __construct($rules) 
    {
        $this->rules = $rules;
    }

    public function areRequirementsMet($ipAddress = null)
    {
        return true;
    }
}