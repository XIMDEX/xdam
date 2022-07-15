<?php

namespace App\Models\CDN;

use App\Models\CDN\DefaultCDNAccess;

class IPAddressCDNAccess extends DefaultCDNAccess
{
    private $ipAddresses;

    public function __construct($parameters)
    {
        parent::__construct($parameters);
    }

    public function areRequirementsMet($ipAddress = null)
    {
        return in_array($ipAddress, $this->parameters->ip_addresses);
    }
}