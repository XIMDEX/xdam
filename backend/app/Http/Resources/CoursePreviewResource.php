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
        $data = @json_decode($this->data);
        $description = property_exists($data, 'description') ? $data->description : [];

        if (!empty($description))
        {
            $name = property_exists($description, 'course_title') ? $description->course_title : '';
            $image = property_exists($description, 'media_upload') ? $description->media_upload : '';
            $introduction = property_exists($description, 'introduction') ? $description->introduction : '';
        }

        return [
            'coursecode' => $this->id,
            'image' => $image,
            'title' => $name,
            'introduction' => $introduction,
        ];
    }
}
