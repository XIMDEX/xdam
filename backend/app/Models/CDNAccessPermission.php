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
    protected $fillable = ['cdn_id', 'type', 'priority'];

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
        $rulesAux = CDNAccessPermissionRule::where('access_permission_id', $this->id)
                        ->where('rule_type', $this->type)
                        ->get();
        $rules = [];

        foreach ($rulesAux as $item) {
            $rules[] = $item->rule;
        }

        return $rules;
    }
}
