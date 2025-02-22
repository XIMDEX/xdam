<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use App\Enums\ResourceType;
use App\Traits\JsonValidatorTrait;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResourceRequest extends FormRequest
{
    use JsonValidatorTrait;


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->type == ResourceType::document) {
            return [
                'type' => ['required', new EnumKey(ResourceType::class)],
                'collection_id' => 'required|exists:collections,id',
                'data' => 'required'
            ];
        } else {
            return [
                MediaType::Preview()->key => 'file',
                'extra' => 'sometimes|nullable',
                'extra.link' => 'string',
                'extra.hover' => 'string',
                'extra.content' => 'string',
                'lang' => 'sometimes|nullable|in:cat,en,es,eu,gl'
            ];
        }
    }

    public function validationData()
    {
        $all = $this->all();
       $all['data'] = json_decode($all['data']);
        if (property_exists($all['data']->description, 'extra')) {
            $all['extra'] = (array) $all['data']->description->extra;
        }
        if (property_exists($all['data']->description, 'lang')) {
            $language = $all['data']->description->lang;
            $all['lang'] = $language === 'ca' ? 'cat' : $language;
        }
        return $all;
    }


    public function prepareForValidation()
    {
      /*  $all = $this->all();
        $castedData = [];
        if (array_key_exists('data', $all)) {
            $castedData = (array)json_decode($all['data']);
        }
        return $this->merge(['data' => $castedData])->all();*/
    }


    public function withValidator($factory)
    {
        $this->throwErrorWithValidator($factory, 'data');
        return $factory;
    }
}
