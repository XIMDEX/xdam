<?php

namespace App\Services;

use App\Models\CDN\CDN;

class CDNService
{
    private $cdnInfo;

    public function __construct()
    {
        $this->cdnInfo = config('cdn.cdns');
    }

    public function getCDNInfo($cdnCode)
    {
        if (!$this->doesCDNExist($cdnCode)) return null;
        return new CDN($this->cdnInfo[$cdnCode]);
    }

    private function doesCDNExist($cdnCode)
    {
        return array_key_exists($cdnCode, $this->cdnInfo);
    }
}