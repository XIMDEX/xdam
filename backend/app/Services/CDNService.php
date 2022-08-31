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
use \Exception;

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
        // $type = AccessPermission::coerce($accessPermissionType);
        // return $type !== null;
        return AccessPermission::existsAccessPermissionType($accessPermissionType);
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

    public function manageAccessPermissionRule($cdnID, $permissionType, $rule, $key,  bool $toRemove)
    {
        $cdnAccessPermission = CDNAccessPermission::where('cdn_id', $cdnID)
                                ->where('type', $permissionType)
                                ->first();

        if ($cdnAccessPermission === null)
            return [$key => false, 'error' => 'The CDN doesn\'t have set the indicated permission access.'];

        if ($permissionType === AccessPermission::default)
            return [$key => false, 'error' => 'The CDN has a free access, and rules don\'t apply.'];

        $ruleMatch = CDNAccessPermissionRule::where('access_permission_id', $cdnAccessPermission->id)
                        ->where('rule', $rule)
                        ->where('rule_type', $permissionType)
                        ->first();

        if ($ruleMatch === null && $toRemove) {
            return [$key => false, 'status' => 'The rule wasn\'t found.'];
        } elseif ($ruleMatch !== null && !$toRemove) {
            return [$key => false, 'status' => 'The rule already exists.'];
        } elseif ($toRemove) {
            $oldRule = CDNAccessPermissionRule::where('access_permission_id', $cdnAccessPermission->id)
                        ->where('rule', $rule)
                        ->where('rule_type', $permissionType)
                        ->delete();
        } else  {
            $newRule = CDNAccessPermissionRule::create([
                            'access_permission_id'  => $cdnAccessPermission->id,
                            'rule'                  => $rule,
                            'rule_type'             => $permissionType
                        ]);
        }

        return [$key => true];
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

    public function generateMultipleDamResourcesHash($cdn, $resourceIDs, $collectionID)
    {
        $results = [];

        foreach ($resourceIDs as $id) {
            $item = DamResource::where('id', $id)
                        ->where('collection_id', $collectionID)
                        ->first();

            if ($item !== null) {
                $auxItem = [
                    'id'    => $item->id,
                    'name'  => $item->name,
                    'type'  => $item->type,
                    'hash'  => $this->generateDamResourceHash($cdn, $item, $collectionID)
                ];
                $results[] = $auxItem;
            }
        }

        return $results;
    }

    public function generateCollectionDamResourcesHash($cdn, $collection)
    {
        $results = [];

        foreach ($collection->resources()->get() as $item) {
            $auxItem = [
                'id'    => $item->id,
                'name'  => $item->name,
                'type'  => $item->type,
                'hash'  => $this->generateDamResourceHash($cdn, $item, $collection->id)
            ];
            $results[] = $auxItem;
        }

        return $results;
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

    public function isCollectionAccessible_v2($collection, $cdn)
    {
        return $cdn->isCollectionAccessible($collection->id);
    }
}