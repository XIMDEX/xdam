<?php

namespace App\Http\Resources;

use App\Enums\ResourceType;
use Illuminate\Database\Eloquent\Collection;
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
        $facets = $request->get('facets', []);
        $corporations = $facets['corporations'] ?? [];
        return [
            "categoryId" => $this['id'],
            "categorytitle" => $this['name'],
            "courses" => $this->courses(explode(',',$request->lang), $corporations),
        ];


    }

    public function courses($lang, $corporations): array
    {
        $courses = [];
        try {
            if ($lang[0] !== "" || count($corporations) > 0) {
                $resources = $this->resources()
                    ->when($lang[0] !== "", function($query) use ($lang) {
                        return $query->whereIn('data->description->language', $lang);
                    })
                    ->when(count($corporations) > 0, function($query) use ($corporations) {
                        return $query->whereIn('data->description->corporations', $corporations);
                    })->get();

            } else {
                $resources = $this->resources;
            }
        } catch (\Throwable $th) {
            $resources = $this['resources'];
        }

        foreach ($resources as $r) {
            $name = "";
            $image = "";
            $introduction = "";
            $data = is_object($r->data) ? $r->data : @json_decode($r->data);
            $description = $data ?? property_exists($data, 'description') ? $data->description : [];

            if (!empty($description))
            {
                $active = property_exists($description, 'active') ? $description->active : null;
                $name = property_exists($description, 'name') ? $description->name : (property_exists($description, 'course_title') ? $description->course_title : '');
                $image = property_exists($description, 'media_upload') ? $description->media_upload : '';
                $introduction = property_exists($description, 'introduction') ? $description->introduction : '';
                $course_source = property_exists($description, 'course_source') ? $description->course_source : '';
                $type = property_exists($description, 'type') ? $description->type : '';
                $external_source = property_exists($description, 'external_source') ? $description->external_source : '';
            }

            if($active) {
                $courses[] = [
                    'coursecode' => $r->id,
                    'image' => $image,
                    'title' => $name,
                    'introduction' => $introduction,
                    'tags' => $r->tags,
                    'course_source' => $course_source,
                    'type' => $type,
                    'external_source' => $external_source,
                    'active' => $active,
                ];
            }
        }
        return $courses;
    }
}
