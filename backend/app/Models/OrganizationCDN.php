<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationCDN extends Model
{
    use HasFactory;

    protected $table = "organization_cdn";

    protected $fillable = ["cdn_id", "organization_id"];

    public function cdn()
    {
        return $this->hasOne(CDN::class, 'id');
    }
}
