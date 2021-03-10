<?php

namespace App\Http\Resources;

use App\Enums\ResourceType;
use Illuminate\Http\Resources\Json\JsonResource;

class ExploreCoursesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            "categoryId" => $this->id,
            "categorytitle" => $this->name,
            "courses" => CoursePreviewResource::collection($this->resources),
        ];
    }
}
