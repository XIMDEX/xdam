<?php

namespace App\Http\Requests;

use App\Models\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AttachResourceToCollectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //check if user is attached to the organization of the collection on $this->all()
        //this means that the user can share resources between organizations
        if($this->organization_id) {
            return true;
        }
        return false;
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $org_of_collection = Collection::where('id', $data['collection_id'])->first()->organization()->first();
        $org = Auth::user()->organizations()->where('organizations.id', $org_of_collection->id)->first();
        $data['organization_id'] = $org->id ?? null;
        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'collection_id' => 'required|exists:collections,id',
            'resource_id' => 'required|exists:dam_resources,id',
            'organization_id' => 'exists:organizations,id'
        ];
    }

}
