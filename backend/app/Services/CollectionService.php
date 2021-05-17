<?php

namespace App\Services;

use App\Models\Collection as ModelsCollection;
use App\Models\DamResource;
use Illuminate\Http\Request;

class CollectionService
{
    public function getLastResourceCreated(ModelsCollection $collection): DamResource
    {
        $lastCreated = $collection->resources()->latest()->first();
        return $lastCreated;
    }
}
