<?php

declare(strict_types=1);

namespace App\Http\Requests\Resource;

use App\Enums\AdditionalBatchSteps;
use App\Enums\Languages;
use App\Models\Collection;
use Illuminate\Foundation\Http\FormRequest;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\UploadedFile;

class StoreResourcesBatchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    private function decodeOptionalData(string $propertyName): array
    {
        return property_exists( $this, $propertyName) 
            ? json_decode($this->$propertyName, true) 
            : [];
    }

    private function filePreviewName(UploadedFile $file): string
    {
        $fileNameWithoutWitheSpace = str_replace(' ', '_', $file->getClientOriginalName());

        return str_replace('.', '_', $fileNameWithoutWitheSpace);
    }

    private function previewFiles(): array
    {
        $allFiles = $this->allFiles();

        unset($allFiles['files']);

        return $allFiles;
    }


    private function generatePreviewFilesValidation(): array
    {
        $all = $this->all();
        
        if(is_null($all['files'])) {
            return [];
        }

        $rules = [];

        foreach($all['files'] as $file) {
            $name = $this->filePreviewName($file);
           
            if(!is_null($all[$name . '_preview'])) {
                $rules[$name . '_preview'] = 'mimes:jpg,bmp,png';
            }
        }

        return $rules;
    }

    public function all($keys = null)
    {
        $parentAll = parent::all($keys);

        $genericResourceDescription = array_key_exists('generic', $parentAll) ? json_decode($parentAll['generic'], true) : [];

        $especificFilesInfoMap = array_key_exists('filesInfo', $parentAll) ? json_decode($parentAll['filesInfo'], true) : [];

        $additionalSteps = !is_null($this->input('additionalSteps')) ? $this->input('additionalSteps') : [];

        return array_merge(
            $parentAll,
            [
                'collectionId' => $this->route('collection_id'),
                'generic' => $genericResourceDescription,
                'filesInfo' => $especificFilesInfoMap,
                'additionalSteps' => $additionalSteps,
            ],
        );
    }

    public function rules(): array
    {
        $previewFilesValidation = $this->generatePreviewFilesValidation();

        $basicRules = [
            'collection' => 'required|numeric|exists:collections,id',

            'workspace' => 'required|string',
            'create_wsp' => 'required|numeric|in:0,1',
            
            'files' => 'required|array',
            'files.*' => 'file',
            
            'previewFiles' => 'sometimes|nullable|array',
            'previewFiles.*' => 'file',
            
            'generic' => 'sometimes|nullable|array',
            'generic.lang' => ['sometimes', 'nullable', new EnumValue(Languages::class)],
            'generic.tags' => 'sometimes|nullable|array',
            'generic.categories' => 'sometimes|nullable|array',
            
            'especificFilesInfoMap' => '',
            
            'aditionalSteps' => 'sometimes|nullable|array',
            'aditionalSteps.*' => [new EnumValue(AdditionalBatchSteps::class)],
        ];

        return array_merge($basicRules, $previewFilesValidation);
    }

    public function validationData(): array
    {

        $allFiles = $this->allFiles();

        $previewFiles = $this->previewFiles();

        return [
            'collection' => $this->route('collection_id'),
            'workspace' => $this->workspace,
            'create_wsp' => $this->create_wsp,
            'files' => $allFiles['files'],
            'previewFiles' => $previewFiles,
            'generic' => $this->decodeOptionalData('generic'),
            'especificFilesInfoMap' => $this->decodeOptionalData('filesInfo'),
            'aditionalSteps' => $this->input('additionalSteps'),
        ];
    }
}
