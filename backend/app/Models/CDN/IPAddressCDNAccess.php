<?php

namespace App\Models\CDN;

use App\Models\CDN\DefaultCDNAccess;

class IPAddressCDNAccess extends DefaultCDNAccess
{
    private $ipAddresses;

    public function __construct($ipAddresses)
    {
        parent::__construct();
        $this->ipAddresses = $ipAddresses;
    }

    public function areRequirementsMet($ipAddress = null)
    {
        return in_array($ipAddress, $this->ipAddresses);
    }
}