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

    public function areRequirementsMet($params )
    {
        $params = array_merge(['ipAddress' => null, 'originURL' => null], $params);
        list('ipAddress' => $ipAddress, 'originUrl' => $originUrl) = $params;

        return in_array($ipAddress, $this->rules);
    }
}