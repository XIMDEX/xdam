<?php

namespace App\Services;

use App\Enums\AccessPermission;
use App\Models\CDN;
use App\Models\CDNCollection;
use App\Models\CDNAccessPermission;
use App\Models\CDNAccessPermissionRule;
use App\Models\Collection;

class CDNService
{
    private $cdnInfo;

    public function __construct()
    {
        $this->cdnInfo = config('cdn.cdns');
    }

    public function createCDN($name)
    {
        $cdn = CDN::create(['name' => $name]);
        $res = $cdn->save();

        if ($res) {
            try {
                $accessPermission = CDNAccessPermission::create(['cdn_id' => $cdn->getID(), 'type' => AccessPermission::default]);
            } catch (Exception $e) {
                return false;
            }
            
            return true;
        }
        
        return false;
    }

    public function removeCDN($cdnID)
    {
        $cdn = CDN::where('id', $cdnID)->delete();
        return true;
    }

    public function existsCDN($cdnID)
    {
        $cdn = CDN::where('id', $cdnID)->first();
        return $cdn !== null;
    }

    public function existsCollection($collectionID)
    {
        $collection = Collection::where('id', $collectionID)->first();
        return $collection !== null;
    }

    public function addCDNCollection($cdnID, $collectionID)
    {
        $cdnCollection = CDNCollection::where('cdn_id', $cdnID)
                            ->where('collection_id', $collectionID)
                            ->first();

        if ($cdnCollection === null) {
            $cdnCollection = CDNCollection::create(['cdn_id' => $cdnID, 'collection_id' => $collectionID]);
        }

        return true;
    }

    public function removeCDNCollection($cdnID, $collectionID)
    {
        $cdnCollection = CDNCollection::where('cdn_id', $cdnID)
                            ->where('collection_id', $collectionID)
                            ->first();

        if ($cdnCollection !== null) {
            $cdnCollection->delete();
            return true;
        }

        return false;
    }

    public function existsAccessPermissionType($accessPermissionType)
    {
        $type = AccessPermission::coerce($accessPermissionType);
        return $type !== null;
    }

    public function updateAccessPermissionType($cdnID, $accessPermissionType)
    {
        $cdnAccessPermission = CDNAccessPermission::where('cdn_id', $cdnID)->first();
        
        if ($cdnAccessPermission === null) return false;
        if ($accessPermissionType !== $cdnAccessPermission->getType()) {
            $deleted = CDNAccessPermissionRule::where('access_permission_id', $cdnAccessPermission->getID())
                        ->delete();
            $cdnAccessPermission->type = $accessPermissionType;
            $cdnAccessPermission->save();
        }

        return true;
    }

    public function addAccessPermissionRule($cdnID, $accessPermissionType, $ipAddress = null, $lti = null)
    {
        $cdnAccessPermission = CDNAccessPermission::where('cdn_id', $cdnID)
                                ->where('type', $accessPermissionType)
                                ->first();

        if ($cdnAccessPermission === null) return false;
        if ($accessPermissionType === AccessPermission::default) return false;

        if ($accessPermissionType === AccessPermission::ipAddress) {
            $lti = null;

            if ($ipAddress === null) return false;
        }

        if ($accessPermissionType === AccessPermission::lti) {
            $ipAddress = null;

            if ($lti === null) return false;
        }

        $rule = CDNAccessPermissionRule::where('access_permission_id', $cdnAccessPermission->getID())
                    ->where('ip_address', $ipAddress)
                    ->where('lti', $lti)
                    ->first();

        if ($rule === null) {
            $rule = CDNAccessPermissionRule::create([
                        'access_permission_id' => $cdnAccessPermission->getID(),
                        'ip_address' => $ipAddress,
                        'lti' => $lti
                    ]);
        }

        return true;
    }

    public function removeAccessPermissionRule($cdnID, $accessPermissionType, $ipAddress = null, $lti = null)
    {
        $cdnAccessPermission = CDNAccessPermission::where('cdn_id', $cdnID)
                                ->where('type', $accessPermissionType)
                                ->first();

        if ($cdnAccessPermission === null) return false;
        if ($accessPermissionType === AccessPermission::default) return false;

        if ($accessPermissionType === AccessPermission::ipAddress) {
            $lti = null;

            if ($ipAddress === null) return false;
        }

        if ($accessPermissionType === AccessPermission::lti) {
            $ipAddress = null;

            if ($lti === null) return false;
        }

        $rule = CDNAccessPermissionRule::where('access_permission_id', $cdnAccessPermission->getID())
                    ->where('ip_address', $ipAddress)
                    ->where('lti', $lti)
                    ->delete();
        return true;
    }

    public function getCDNInfo($cdnCode)
    {
        return CDN::where('id', $cdnCode)->first();
    }
}