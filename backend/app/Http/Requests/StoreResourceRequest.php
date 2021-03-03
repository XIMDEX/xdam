<?php

namespace App\Http\Requests;

use App\Enums\ResourceType;
use App\Models\Collection;
use App\Models\Workspace;
use App\Traits\JsonValidatorTrait;
use BenSampo\Enum\Rules\EnumKey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreResourceRequest extends FormRequest
{
    use JsonValidatorTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //Check if collection_id is attached to the organization of user selected workspace
        $selected_workspace = Auth::user()->selected_workspace;
        $collection = Collection::find($this->collection_id);
        $org = Workspace::find($selected_workspace)->organization()->first();
        if($collection->organization()->first()->id == $org->id) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => ['required', new EnumKey(ResourceType::class)],
            'collection_id' => 'required|exists:collections,id',
            'data' => 'required'
        ];
    }

    public function prepareForValidation()
    {
        $all = $this->all();
        $castedData = [];
        if (array_key_exists("data", $all)) {
            $castedData = json_decode($all["data"]);
        }
        return $this->merge(["data" => $castedData])->all();
    }

    public function withValidator($factory)
    {
        $this->throwErrorWithValidator($factory,  "data");
        return $factory;
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        if($this->route('collection_id')) {
            $data['collection_id'] = $this->route('collection_id');
        }
        return $data;
    }


}
