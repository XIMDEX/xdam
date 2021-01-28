<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CoursePreviewCollection extends ResourceCollection
{
    public $collects = CoursePreviewResource::class;
}
