<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CDNAccessPermissionRule extends Model
{
    use HasFactory;

    protected $table = 'access_permission_rules';
    protected $fillable = ['access_permission_id', 'rule', 'rule_type'];

    public function accessPermission(): BelongsTo
    {
        return $this->belongsTo(CDNAccessPermission::class);
    }
}
