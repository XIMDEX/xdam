<?php

namespace App\Models;

use App\Enums\AccessPermission;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CDNAccessPermission extends Model
{
    use HasFactory;

    protected $table = 'access_permissions';
    protected $fillable = ['cdn_id', 'type'];

    public function getID()
    {
        return $this->attributes['id'];
    }

    public function getType()
    {
        return $this->attributes['type'];
    }

    public function cdn(): BelongsTo
    {
        return $this->belongsTo(CDN::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(CDNAccessPermissionRule::class);
    }

    public function getRules()
    {
        $rulesAux = CDNAccessPermissionRule::where('access_permission_id', $this->getID())->get();
        $rules = [];

        foreach ($rulesAux as $item) {
            switch ($this->getType()) {
                case AccessPermission::ipAddress:
                    $rules[] = $item->getIPAddress();
                    break;

                case AccessPermission::lti:
                    $rules[] = $item->getLTI();
                    break;
            }
        }

        return $rules;
    }
}
