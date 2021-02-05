<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WorkspaceCollection extends ResourceCollection
{
    public $collects = WorkspaceResource::class;
}
