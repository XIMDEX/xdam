<?php

namespace App\Services;

use App\Models\CDN;

class CDNService
{
    private $cdnInfo;

    public function __construct()
    {
        $this->cdnInfo = config('cdn.cdns');
    }

    public function getCDNInfo($cdnCode)
    {
        return CDN::where('id', $cdnCode)->first();
    }
}