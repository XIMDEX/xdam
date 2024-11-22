<?php

namespace App\Http\Controllers;

use App\Models\DamResource;
use App\Services\Amazon\GetAmazonResourceMetadataService;
use App\Services\Amazon\GetAmazonResourceService;
use App\Services\Amazon\SaveAmazonResourceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ResourceAmazonController extends Controller
{
    private SaveAmazonResourceService $saveAmazonResourceService;
    private GetAmazonResourceService $getAmazonResourceService;
    private GetAmazonResourceMetadataService $getAmazonResourceMetadataService;

    public function __construct(SaveAmazonResourceService $saveAmazonResourceService, GetAmazonResourceService $getAmazonResourceService, GetAmazonResourceMetadataService $getAmazonResourceMetadataService)
    {
        $this->saveAmazonResourceService = $saveAmazonResourceService;
        $this->getAmazonResourceService = $getAmazonResourceService;
        $this->getAmazonResourceMetadataService = $getAmazonResourceMetadataService;

    }
    public function save(Request $request)
    {
        $remoteFile = $this->getAmazonResourceService->getResourceByCurl($request->urlFile);
        $files = $request->allFiles();
        $files['remoteFile'] = $remoteFile;
        $resource = $this->saveAmazonResourceService->save($request->urlFile, $request->nameFile, $request->metadata,  $files);

        return $resource;
        //return response(new ResourceAmazonResource($resource))->setStatusCode(Response::HTTP_OK);
    }

    public function getMetadata(DamResource $damResource,string $fileName){
        try {
            $metadata = $this->getAmazonResourceMetadataService->getMetadata($damResource, $fileName);
            return response($metadata)->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
