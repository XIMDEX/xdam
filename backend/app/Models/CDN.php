<?php

namespace App\Models;

use App\Enums\AccessPermission;
use App\Models\CDNAccessPermission;
use App\Models\CDNCollection;
use App\Models\CDN\DefaultCDNAccess;
use App\Models\CDN\IPAddressCDNAccess;
use App\Models\CDN\LTICDNAccess;
use App\Models\CDN\OriginURLAccess;
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

    public function isCollectionAccessible($collectionID)
    {
        $collections = CDNCollection::where('cdn_id', $this->id)->get();

        foreach ($collections as $item)
            if ($item->collection_id === $collectionID)
                return true;

        return false;
    }

    public function checkAccessRequirements($ipAddress = null, $originURL = null)
    {
        $accessPermission = $this->getAccessPermission();
        return $accessPermission->areRequirementsMet($ipAddress, $originURL);
    }
    
    private function getAccessPermission(): DefaultCDNAccess
    {
        $cdnAccessPermission = $this->cdn_access_permission()->first();
        $rules = $cdnAccessPermission->getRules();

        switch ($cdnAccessPermission->type) {
            case AccessPermission::default:
                return new DefaultCDNAccess($rules);

            case AccessPermission::ipAddress:
                return new IPAddressCDNAccess($rules);

            case AccessPermission::lti:
                return new LTICDNAccess($rules);
            
            case AccessPermission::originUrl:
                return new OriginURLAccess($rules);
        }

        return null;
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
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'hash'  => $this->resource_hash
        ];
    }
}
