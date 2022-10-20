<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CDNHash extends Model
{
    use HasFactory;

    protected $table = "cdn_hashes";

    protected $fillable = ["cdn_id", "resource_id", "collection_id", "resource_hash", "total_visits", "last_visited_at"];

    public function increaseVisitsCounter()
    {
        $this->total_visits++;
        $this->last_visited_at = time();
        $this->update();
    }
}
