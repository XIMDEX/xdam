<?php

namespace App\Http\Requests;

use App\Enums\ResourceType;
use App\Models\Collection;
use App\Traits\JsonValidatorTrait;
use BenSampo\Enum\Rules\EnumKey;
use Exception;
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
        $user = Auth::user();
        $wsp = $user->workspaces()->where('workspaces.id', $user->selected_workspace)->first();
        $org = $wsp->organization()->first();
        $collection = Collection::find($this->collection_id);

        if($this->type != $collection->accept) {
            throw new Exception("The resource type isn't accepted for the collection");
        }

        if($collection->organization()->first()->id == $org->id) {
            return true;
        } else {
            $collections_available = $org->collections()->get();
            $colls = [];
            foreach ($collections_available as $coll) {
                $colls[] = $coll->id;
            }
            throw new Exception('Invalid collection_id. Available for selected workspace: '. implode(', ', $colls));
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
