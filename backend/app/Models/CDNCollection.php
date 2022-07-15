<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CDNCollection extends Model
{
    use HasFactory;

    protected $table = "cdns_collections";

    public function collectionIDMatches($collectionID) {
        return $this->attributes['collection_id'] == $collectionID;
    }
}
