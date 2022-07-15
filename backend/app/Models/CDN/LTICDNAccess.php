<?php

namespace App\Models\CDN;

use App\Models\CDN\DefaultCDNAccess;

class LTICDNAccess extends DefaultCDNAccess
{
    public function __construct($parameters)
    {
        parent::__construct($parameters);
    }
}