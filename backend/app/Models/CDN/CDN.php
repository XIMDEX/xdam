<?php

namespace App\Models\CDN;

use App\Models\CDN\DefaultCDNAccess;
use App\Models\CDN\IPAddressCDNAccess;
use App\Models\CDN\LTICDNAccess;
use App\Models\Collection;

class CDN
{
    private $collections;
    private $access;

    public function __construct($parameters)
    {
        $ipAddresses = isset($parameters['ip_addresses']) ? $parameters['ip_addresses'] : null;
        $this->collections = $parameters['collections'];
        $this->access = $this->getAccessInstance($parameters['access'], $ipAddresses);
    }

    public function isCollectionAccessible($collection)
    {
        return in_array($collection, $this->collections);
    }

    public function checkAccessRequirements($ipAddress = null)
    {
        return $this->access->areRequirementsMet($ipAddress);
    }

    private function getCollections($collectionsList)
    {
        $collections = [];
        foreach ($collectionsList as $item) {
            $aux = Collection::where('id', $item)->first();
            if ($aux != null) $collections[] = $aux;
        }

        return $collections;
    }

    private function getAccessInstance($accessID, $ipAddresses = null)
    {
        switch ($accessID) {
            case 'DEFAULT':
                return new DefaultCDNAccess();

            case 'IP ADDRESS':
                return new IPAddressCDNAccess($ipAddresses);

            case 'LTI':
                return new LTICDNAccess();
        }

        return null;
    }
}