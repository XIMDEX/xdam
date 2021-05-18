<?php

namespace App\Services;

use App\Models\Collection as ModelsCollection;
use App\Models\DamResource;
use Illuminate\Http\Request;

class CollectionService
{
    public function getLastResource(ModelsCollection $collection, $time): DamResource
    {
        $last = $collection->resources()->orderBy($time.'_at', 'desc')->first();
        return $last;
    }
}
