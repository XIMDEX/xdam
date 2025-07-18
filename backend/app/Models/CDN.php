<?php

namespace App\Models;

use App\Enums\AccessPermission;
use App\Models\CDNAccessPermission;
use App\Models\CDNCollection;
use App\Models\CDN\DefaultCDNAccess;
use App\Models\CDN\IPAddressCDNAccess;
use App\Models\CDN\LTICDNAccess;
use App\Models\CDN\OriginURLAccess;
use App\Models\CDN\WorkspaceAccess;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CDN extends Model
{
    use HasFactory;

    protected $table = "cdns";
    protected $fillable = ['uuid', 'name'];
    private $resource_hash = null;

    public function getID()
    {
        return $this->attributes['id'];
    }

    public function cdn_access_permission(): HasOne
    {
        return $this->hasOne(CDNAccessPermission::class, 'cdn_id');
    }

    public function cdn_collections(): HasMany
    {
        return $this->hasMany(CDNCollection::class, 'cdn_id');
    }

    public function isCollectionAccessible(int $collectionID)
    {
        $collectionIds = CDNCollection::where('cdn_id', $this->id)
            ->pluck('collection_id')
            ->toArray();
    
        return in_array($collectionID, $collectionIds, true);
    }

    public function checkAccessRequirements($params = ['ipAddress' => null, 'originURL' => null])
    {
        $permissions = $this->getAccessPermission();

        foreach ($permissions as $permission) {
            if ($permission->areRequirementsMet($params)) {
                return true;
            }
        }

        return false;
    }
    
    private function getAccessPermission(): array
    {
        $accessPermissions = [];
        $permissions = $this->cdn_access_permission()->get();

        foreach ($permissions as $permission) {
            $pAux = null;
            $pRules = $permission->getRules();

            switch ($permission->type) {
                case AccessPermission::default:
                    $pAux = new DefaultCDNAccess($pRules);
                    break;

                case AccessPermission::ipAddress:
                    $pAux = new IPAddressCDNAccess($pRules);
                    break;

                case AccessPermission::lti:
                    $pAux = new LTICDNAccess($pRules);
                    break;

                case AccessPermission::originUrl:
                    $pAux = new OriginURLAccess($pRules);
                    break;
                
                case AccessPermission::workspace:
                    $pAux = new WorkspaceAccess($pRules);
                    break;
                
                default:
                    break;
            }

            $accessPermissions[] = $pAux;
        }

        return $accessPermissions;
    }

    public function setHash($hash)
    {
        $this->resource_hash = $hash;
    }

    public function getHash()
    {
        return $this->resource_hash;
    }

    public function toArray()
    {
        $permissionType = $this->cdn_access_permission()->first();

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'permission_type'   => $permissionType->type,
            'hash'              => $this->resource_hash
        ];
    }
}
