<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Silber\Bouncer\Database\Models;
use Silber\Bouncer\Database\Role as BouncerRole;

class Role extends BouncerRole
{
    use HasFactory;
    protected $fillable = ['name', 'title', 'level','organization_id', 'applicable_to_entity'];

    /**
     * Get the organization that ow
     *
     * @return \Illuminate\Database\Relations\BelongsTo
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
