<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MediaCollection extends ResourceCollection
{
    public $collects = MediaResource::class;
}
