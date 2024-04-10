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
    const SEPARATOR_TOKEN = '#x#';

    /**
     * CDNService constructor.
     */
    public function __construct()
    {
        $this->cdnInfo = config('cdn.cdns');
    }

    /**
     * Creates a CDN
     * @param string $name
     * @return boolean
     */
    public function createCDN(string $name)
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

    /**
     * Removes a CDN
     * @param int $cdnID
     * @return boolean
     */
    public function removeCDN(int $cdnID)
    {
        $cdn = CDN::where('id', $cdnID)->delete();
        return true;
    }

    /**
     * Checks if a CDN exists
     * @param int $cdnID
     * @return boolean
     */
    public function existsCDN(int $cdnID)
    {
        $cdn = CDN::where('id', $cdnID)->first();
        return $cdn !== null;
    }

    /**
     * Check if a collection exists
     * @param int $collectionID
     * @return boolean
     */
    public function existsCollection(int $collectionID)
    {
        $collection = Collection::where('id', $collectionID)->first();
        return $collection !== null;
    }

    /**
     * Attaches a collection to a CDN
     * @param int $cdnID
     * @param int $collectionID
     * @return boolean
     */
    public function addCDNCollection(int $cdnID, int $collectionID)
    {
        $cdnCollection = CDNCollection::where('cdn_id', $cdnID)
                            ->where('collection_id', $collectionID)
                            ->first();

        if ($cdnCollection === null) {
            $cdnCollection = CDNCollection::create(['cdn_id' => $cdnID, 'collection_id' => $collectionID]);
        }

        return true;
    }

    /**
     * Deattaches a collection from a CDN
     * @param int $cdnID
     * @param int $collectionID
     * @return boolean
     */
    public function removeCDNCollection(int $cdnID, int $collectionID)
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

    /**
     * Checks if a collection is attached to a CDN
     * @param int $cdnID
     * @param int $collectionID
     * @return boolean
     */
    public function checkCDNCollection(int $cdnID, int $collectionID)
    {
        $cdnCollection = CDNCollection::where('cdn_id', $cdnID)
                            ->where('collection_id', $collectionID)
                            ->first();
        return $cdnCollection !== null;
    }

    /**
     * Gets the collections attached to a CDN
     * @param int $cdnID
     * @return array
     */
    public function getCDNCollections(int $cdnID)
    {
        $collections = [];
        $cdnCollections = CDNCollection::where('cdn_id', $cdnID)
                            ->orderBy('collection_id')
                            ->get();

        foreach ($cdnCollections as $item) {
            $collections[] = $item->collection_id;
        }

        return $collections;
    }

    /**
     * Checks if the access permission type exists
     * @param string $accessPermissionType
     * @return boolean
     */
    public function existsAccessPermissionType(string $accessPermissionType)
    {
        return AccessPermission::existsAccessPermissionType($accessPermissionType);
    }

    /**
     * Updates the access permission type
     * @param int $cdnID
     * @param array $accessPermissionTypes
     * @return boolean
     */
    public function updateAccessPermissionType(int $cdnID, array $accessPermissionTypes)
    {
        CDNAccessPermission::where('cdn_id', $cdnID)->delete();
        $priority = 1;

        foreach ($accessPermissionTypes as $type) {
            $entry = CDNAccessPermission::create([
                'cdn_id' => $cdnID,
                'type' => $type,
                'priority' => $priority
            ]);
            $priority++;
        }

        return true;
    }

    /**
     * Manages access permission rules
     * @param int $cdnID
     * @param string $permissionType
     * @param string $rule
     * @param string $key
     * @param boolean $toRemove
     * @return array
     */
    public function manageAccessPermissionRule(
        int $cdnID,
        string $permissionType,
        string $rule,
        string $key,
        bool $toRemove
    ) {
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
            CDNAccessPermissionRule::where('access_permission_id', $cdnAccessPermission->id)
                ->where('rule', $rule)
                ->where('rule_type', $permissionType)
                ->delete();
        } else  {
            CDNAccessPermissionRule::create([
                'access_permission_id'  => $cdnAccessPermission->id,
                'rule'                  => $rule,
                'rule_type'             => $permissionType
            ]);
        }

        return [$key => true];
    }

    /**
     * Returns the access permission rules
     * @param int $cdnID
     * @return array
     */
    public function getAccessPermissionRules(int $cdnID)
    {
        $rules = [];
        $permissions = CDNAccessPermission::where('cdn_id', $cdnID)
                        ->orderBy('priority')
                        ->get();

        foreach ($permissions as $permission) {
            $auxItem = [
                'priority' => $permission->priority,
                'type' => $permission->type,
                'rules' => []
            ];

            $pRules = CDNAccessPermissionRule::where('access_permission_id', $permission->id)
                        ->where('rule_type', $permission->type)
                        ->get();

            foreach ($pRules as $pRule) {
                $auxItem['rules'][] = $pRule->rule;
            }

            $rules[] = $auxItem;
        }

        return $rules;
    }

    /**
     * Returns the CDN by its ID
     * @param $cdnCode
     * @return CDN
     */
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
        } else {
            $resource = DamResource::where('id', $hash)
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

    public function decodeHash($hash): Array
    {
        $decode_hash = base64_decode($hash);
        $token = urldecode($decode_hash);
        $data = explode(SELF::SEPARATOR_TOKEN, $token);


        return [
            'damResourceHash' => $data[0],
            'workspaceID' => $data[1] ?? false,
            'areaID' => $data[2] ?? false,
            'isDownloadable' => $data[3] ?? false
        ];
    }

    public function encodeHash($damResourceHash, $workspaceID, $tagAreaID, $isDownloable): String
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $resource = $this->getAttachedDamResource($damResourceHash);
        // $userAbilities = $resource->getUserAbilities(null);
        $workspaces = $resource->workspaces()->get();

        $token_data = [$damResourceHash, $workspaceID, $tagAreaID, $isDownloable ? 1 : 0];
        $token_decoded = join(self::SEPARATOR_TOKEN, $token_data);

        return base64_encode(urlencode($token_decoded));
    }

    public function hasAccessPersmission($permission, $cdnID)
    {
        $access_permissions = $this->getAccessPermissionRules($cdnID);
        $hasPermission = false;
        foreach ($access_permissions as $access_permission) {
            if ($access_permission['type'] === $permission) {
                $hasPermission = true;
                break;
            }
        }
        return $hasPermission;
    }
}
