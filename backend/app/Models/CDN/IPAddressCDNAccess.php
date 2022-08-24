<?php

namespace App\Models\CDN;

use App\Models\CDN\DefaultCDNAccess;

class IPAddressCDNAccess extends DefaultCDNAccess
{
    private $ipAddresses;

    public function __construct($rules)
    {
        parent::__construct($rules);
    }

    public function areRequirementsMet($ipAddress = null)
    {
        return in_array($ipAddress, $this->rules);
    }
}