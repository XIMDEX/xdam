<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CDNCollection extends Model
{
    use HasFactory;

    protected $table = "cdns_collections";
    protected $fillable = ['cdn_id', 'collection_id'];

    public function cdn(): BelongsTo
    {
        return $this->belongsTo(CDN::class);
    }

    public function collection(): HasOne
    {
        return $this->hasOne(Collection::class);
    }

    public function collectionIDMatches($collectionID) {
        return $this->attributes['collection_id'] == $collectionID;
    }
}
