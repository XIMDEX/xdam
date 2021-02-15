<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->collection_id) {
            foreach (Auth::user()->organizations()->get() as $org) {
                foreach ($org->collections()->get() as $collection) {
                    if($collection->id != $this->collection_id) {
                        continue;
                    } else {
                        return true;
                    }
                }
            }
            return false;
        }
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
            'Preview' => 'file',
            'data' => 'string',
            'type' => ['required', new EnumKey(ResourceType::class)],
        ];
    }

}
