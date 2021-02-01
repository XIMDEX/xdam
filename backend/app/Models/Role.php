<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Role as SpatieRole;


class Role extends SpatieRole
{
    use HasFactory, UsesUuid;

    public static function findByUuId(string $id, $guardName = null)
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (! $role) {
            throw new Exception("Role doesn't exist");
        }
        return $role;
    }
}
