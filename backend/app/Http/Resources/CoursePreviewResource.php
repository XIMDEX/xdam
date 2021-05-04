<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CoursePreviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $name = "";
        $image = "";
        $introduction = "";
        $data = is_object($this->data) ? $this->data : @json_decode($this->data);
        $description = $data ?? property_exists($data, 'description') ? $data->description : [];

        if (!empty($description))
        {
            $active = property_exists($description, 'active') ? $description->active : null;
            $name = property_exists($description, 'name') ? $description->name : '';
            $image = property_exists($description, 'media_upload') ? $description->media_upload : '';
            $introduction = property_exists($description, 'introduction') ? $description->introduction : '';
            $course_source = property_exists($description, 'course_source') ? $description->course_source : '';
            $type = property_exists($description, 'type') ? $description->type : '';
            $external_source = property_exists($description, 'external_source') ? $description->external_source : '';
        }
        if($active) {
            return [
                'coursecode' => $this->id,
                'image' => $image,
                'title' => $name,
                'introduction' => $introduction,
                'tags' => $this->tags,
                'course_source' => $course_source,
                'type' => $type,
                'external_source' => $external_source,
                'active' => $active,
            ];
        } else {
            return false;
        }
    }
}
