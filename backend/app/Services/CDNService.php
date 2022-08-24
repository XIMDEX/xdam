<?php

namespace App\Services;

use App\Enums\AccessPermission;
use App\Models\CDN;
use App\Models\CDNCollection;
use App\Models\CDNAccessPermission;
use App\Models\CDNAccessPermissionRule;
use App\Models\Collection;
use App\Models\DamResource;
use Illuminate\Support\Str;

class CDNService
{
    private $cdnInfo;

    public function __construct()
    {
        $this->cdnInfo = config('cdn.cdns');
    }

    public function createCDN($name)
    {
        $cdn = CDN::create([
            'uuid' => Str::uuid(),
            'name' => $name]
        );
        $res = $cdn->save();

        if ($res) {
            try {
                $accessPermission = CDNAccessPermission::create([
                    'cdn_id' => $cdn->id,
                    'type' => AccessPermission::default
                ]);
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
            $deleted = CDNAccessPermissionRule::where('access_permission_id', $cdnAccessPermission->id)
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

        $rule = CDNAccessPermissionRule::where('access_permission_id', $cdnAccessPermission->id)
                    ->where('ip_address', $ipAddress)
                    ->where('lti', $lti)
                    ->first();

        if ($rule === null) {
            $rule = CDNAccessPermissionRule::create([
                        'access_permission_id' => $cdnAccessPermission->id,
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

        $rule = CDNAccessPermissionRule::where('access_permission_id', $cdnAccessPermission->id)
                    ->where('ip_address', $ipAddress)
                    ->where('lti', $lti)
                    ->delete();
        return true;
    }

    public function getCDNInfo($cdnCode)
    {
        return CDN::where('id', $cdnCode)->first();
    }

    public function generateDamResourceHash($cdn, $resource, $collectionID)
    {
        $hash = substr($resource->id, 0, 3) . substr($cdn->uuid, 14, 4) . $collectionID . substr($resource->id, 14, 4) . substr($cdn->uuid, -3);
        return $hash;
    }

    public function getAttachedDamResource($cdn, $hash)
    {
        $resourceMatch = $cdnMatch = null;

        $resourcePart1 = substr($hash, 0, 3);
        $cdnPart1 = substr($hash, 3, 4);
        $collectionID = intval(substr($hash, 7, 1));
        $resourcePart2 = substr($hash, 8, 4);
        $cdnPart2 = substr($hash, 12, 4);

        $resourcesRes = DamResource::where('id', 'LIKE', $resourcePart1 . '%')
                            ->where('id', 'LIKE', '%' . $resourcePart2 . '%')
                            ->where('collection_id', $collectionID)
                            ->get();
        $cdnRes = CDN::where('uuid', 'LIKE', '%' . $cdnPart1 . '%')
                    ->where('uuid', 'LIKE', '%' . $cdnPart2 . '%')
                    ->get();

        foreach ($resourcesRes as $item) {
            if (substr($item->id, 0, 3) == $resourcePart1
                    && substr($item->id, 14, 4) == $resourcePart2)
                $resourceMatch = $item;
        }
        
        foreach ($cdnRes as $item) {
            if ($item->uuid === $cdn->uuid) $cdnMatch = $item;
        }

        if ($cdnMatch === null) return null;

        return $resourceMatch;
    }

    public function isCollectionAccessible($resource, $cdn)
    {
        return $cdn->isCollectionAccessible($resource->collection_id);
    }
}