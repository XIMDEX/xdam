<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = ['type_id', 'name', 'organization_id', 'solr_connection', 'accept', 'max_number_of_files'];
    protected $table = "collections";
    public const UNLIMITED_FILES = "unlimited";

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function resources()
    {
        return $this->hasMany(DamResource::class);
    }

    public function cdn_collections(): BelongsToMany
    {
        return $this->belongsToMany(CDNCollection::class);
    }

    public function getMaxNumberOfFiles()
    {
        if ($this->max_number_of_files === null || $this->max_number_of_files <= 0) return self::UNLIMITED_FILES;
        return $this->max_number_of_files;
    }
}
