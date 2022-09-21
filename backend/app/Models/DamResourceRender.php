<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamResourceRender extends Model
{
    use HasFactory;

    protected $table = "dam_resources_renders";

    protected $fillable = ["resource_id", "remote_key", "total_renders"];

    public function increaseTotalRendersCount()
    {
        $this->total_renders++;
        $this->update();
    }
}
