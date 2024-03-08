<?php

namespace App\Models\CDN;

use App\Models\CDN\DefaultCDNAccess;

class OriginURLAccess extends DefaultCDNAccess
{
    public function __construct($rules)
    {
        parent::__construct($rules);
    }

    public function areRequirementsMet($params )
    {
        $params = array_merge(['ipAddress' => null, 'originURL' => null], $params);
        list('ipAddress' => $ipAddress, 'originUrl' => $originUrl) = $params;

        foreach ($this->rules as $rule) {
            if ($this->checkOriginURL($rule, $originURL)) return true;
        }
        return false;
    }

    private function checkOriginURL($rule, $originURL)
    {
        $ruleCleaned = $this->cleanURL($rule);
        $originURLCleaned = $this->cleanURL($originURL);
        if ($rule === $originURL) return true;
        return $ruleCleaned === $originURLCleaned;
    }

    private function cleanURL($url)
    {
        $url = explode('?', $url)[0];
        $url = str_replace(['http://www.', 'https://wwww.', 'http://', 'https://'], '', $url);
        $finalChrs = -1;
        $finished = false;

        for ($i = strlen($url) - 1; $i >= 0; $i--) {
            if ($url[$i] == '/' && !$finished) {
                $finalChrs = $i;
            } else {
                $finished = true;
            }
        }

        if ($finalChrs != -1) $url = substr($url, 0, $finalChrs);
        return $url;
    }
}