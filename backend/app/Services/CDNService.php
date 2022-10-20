<?php

namespace App\Services;

use App\Enums\AccessPermission;
use App\Models\CDN;
use App\Models\CDNHash;
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

    public function checkCDNCollection($cdnID, $collectionID)
    {
        $cdnCollection = CDNCollection::where('cdn_id', $cdnID)
                            ->where('collection_id', $collectionID)
                            ->first();
        return $cdnCollection !== null;
    }

    public function getCDNCollections($cdnID)
    {
        $collections = [];
        $cdnCollections = CDNCollection::where('cdn_id', $cdnID)
                            ->get();

        foreach ($cdnCollections as $item) {
            $collections[] = $item->collection_id;
        }

        return $collections;
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
        if ($accessPermissionType !== $cdnAccessPermission->type) {
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
        if (($hash = $this->getExistingDamResourceHash($cdn, $resource, $collectionID)) !== null) return $hash;
        $error = true;
        $limit = 30;
        $current = 0;

        while ($error) {
            $hash = $this->getCurrentResourceHashAttempt($cdn, $resource, $collectionID, $current);
            if ($this->registerResourceHash($cdn, $resource, $collectionID, $hash)) $error = false;
            $current++;
            if ($current >= $limit && $error) return null;
        }

        return $hash;
    }

    private function getExistingDamResourceHash($cdn, $resource, $collectionID)
    {
        $item = CDNHash::where('cdn_id', $cdn->id)
                    ->where('resource_id', $resource->id)
                    ->where('collection_id', $collectionID)
                    ->first();

        if ($item !== null) return $item->resource_hash;
        return null;
    }

    private function getCurrentResourceHashAttempt($cdn, $resource, $collectionID, $attempt)
    {
        $factor = random_int(1, 5000);
        $value = (string) (time() * intdiv(($attempt * $factor + $collectionID * $factor), $collectionID));
        $hash = substr(Str::orderedUuid(), -2, 2) . substr($resource->id, 0, 3) . substr($cdn->uuid, 14, 4)
                . substr($value, random_int(0, strlen($value) - 1), 2) . substr($resource->id, 14, 4) . substr($cdn->uuid, -3)
                . substr(Str::orderedUuid(), -2, 2);
        return $hash;
    }

    private function registerResourceHash($cdn, $resource, $collectionID, $resourceHash)
    {
        try {
            $register = CDNHash::create([
                'cdn_id'        => $cdn->id,
                'resource_id'   => $resource->id,
                'collection_id' => $collectionID,
                'resource_hash' => $resourceHash
            ]);
        } catch (\Exception $e) {
            // echo $e->getMessage();
            return false;
        }

        return true;
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

    public function getAttachedDamResource($hash)
    {
        $match = CDNHash::where('resource_hash', $hash)
                    ->first();
        
        if ($match !== null) {
            $resource = DamResource::where('id', $match->resource_id)
                            ->first();
            return $resource;
        }

        return null;
    }

    public function getCDNAttachedToDamResource($hash, $resource)
    {
        $match = CDNHash::where('resource_hash', $hash)
                    ->where('resource_id', $resource->id)
                    ->first();

        if ($match !== null) {
            $cdn = CDN::where('id', $match->cdn_id)
                        ->first();
            return $cdn;
        }

        return null;
    }

    public function isCollectionAccessible($resource, $cdn)
    {
        return $cdn->isCollectionAccessible($resource->collection_id);
    }

    public function isCollectionAccessible_v2($collection, $cdn)
    {
        return $cdn->isCollectionAccessible($collection->id);
    }

    public function getCDNsAttachedToCollection(Collection $collection)
    {
        $cdns = [];
        $matches = CDNCollection::where('collection_id', $collection->id)
                        ->get();

        foreach ($matches as $result) {
            $cdns[] = $result->cdn()->first();
        }

        return $cdns;
    }
}