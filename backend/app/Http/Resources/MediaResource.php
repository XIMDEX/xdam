<?php

namespace App\Http\Resources;

use App\Enums\MediaType;
use App\Enums\ThumbnailTypes;
use App\Utils\DamUrlUtil;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $thumbnailsTypes = ThumbnailTypes::getValues();
        $thumbnails = [];
        if ($this->collection_name == MediaType::Preview()->key)
        {
            foreach($thumbnailsTypes as $thumbnailType)
            {
                $hasPreview = $this->hasGeneratedConversion($thumbnailType);
               $thumbnails[$thumbnailType] = $hasPreview;
            }
        }


        $parent_id = $this->hasCustomProperty('parent_id') ? $this->getCustomProperty('parent_id') : "";

        return [
            'id' => $this->id,
            'dam_url' => DamUrlUtil::generateDamUrl($this, $parent_id),
            'parent_id' => $parent_id,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'thumbnails' => $thumbnails,
        ];
    }
}
