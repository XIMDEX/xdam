<?php

namespace App\Models;

use App\Enums\AccessPermission;
use App\Models\CDNCollection;
use App\Models\CDN\DefaultCDNAccess;
use App\Models\CDN\IPAddressCDNAccess;
use App\Models\CDN\LTICDNAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CDN extends Model
{
    use HasFactory;

    protected $table = "cdns";

    public function isCollectionAccessible($collectionID)
    {
        $collections = CDNCollection::where('cdn_id', $this->attributes['id'])->get();
        foreach ($collections as $item) {
            if ($item->collectionIDMatches($collectionID)) {
                return true;
            }
        }

        return false;
    }

    public function checkAccessRequirements($ipAddress = null)
    {
        $accessPermission = $this->getAccessPermission($this->attributes['access_permission'],
                                                        $this->attributes['access_permission_properties']);
        return $accessPermission->areRequirementsMet($ipAddress);
    }
    
    private function getAccessPermission($accessPermission, $properties): DefaultCDNAccess
    {
        switch ($accessPermission) {
            case AccessPermission::default:
                return new DefaultCDNAccess($properties);

            case AccessPermission::ipAddress:
                return new IPAddressCDNAccess($properties);

            case AccessPermission::lti:
                return new LTICDNAccess($properties);
        }

        return null;
    }
}
