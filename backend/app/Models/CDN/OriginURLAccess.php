<?php

namespace App\Models\CDN;

use App\Models\CDN\DefaultCDNAccess;

class OriginURLAccess extends DefaultCDNAccess
{
    public function __construct($rules)
    {
        parent::__construct($rules);
    }

    public function areRequirementsMet($ipAddress = null, $originURL = null)
    {
        return in_array($originURL, $this->rules);
    }
}