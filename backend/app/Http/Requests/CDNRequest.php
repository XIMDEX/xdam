<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\DamResource;
use App\Models\Collection;
use App\Models\CDNCollection;

class CDNRequest extends FormRequest
{
    public function getAttachedDamResource()
    {
        $info = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', base64_decode($this->damResourceHash)));
        $resource = DamResource::where('id', $info->resource_id)
                        ->where('collection_id', $info->collection_id)
                        ->first();
        $this->collection_id = $info->collection_id;
        $this->resource_id = $info->resource_id;

        return $resource;
    }

    public function isCollectionAccessible($resource, $cdn)
    {
        return $cdn->isCollectionAccessible($this->collection_id);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can(Abilities::ReadResourceReport, Workspace::find($this->user()->selected_workspace)) ?? false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
