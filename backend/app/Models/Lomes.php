<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lomes extends Model
{
    use HasFactory;

    protected $table = "resource_lomes";
    protected $guarded = ['id'];

    public function getResourceLOMValues()
    {
        $exceptions = ['id', 'dam_resource_id', 'created_at', 'updated_at'];
        $attributesValues = $this->attributesToArray();
        $resourceInfo = [];

        foreach ($attributesValues as $key => $value) {
            if (!in_array($key, $exceptions)) {
                $resourceInfo[$key] = $value;
            }
        }

        return $resourceInfo;
    }
}
