<?php

namespace App\Http\Controllers;

use App\Http\Resources\ResourceResource;
use App\Models\Collection;
use App\Services\CollectionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CollectionController extends Controller
{
    private CollectionService $collectionService;

    public function __construct(CollectionService $collectionService)
    {
        $this->collectionService = $collectionService;
    }

    public function getLastResourceCreated(Collection $collection)
    {
        $damResource = $this->collectionService->getLastResource($collection, 'created');
        return (new ResourceResource($damResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function getLastResourceUpdated(Collection $collection)
    {
        $damResource = $this->collectionService->getLastResource($collection, 'updated');
        return (new ResourceResource($damResource))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function get(Request $request)
    {
        $collectionId = $request->route('collection_id');

        return $this->collectionService->get($collectionId);
    }

    public function createOrganizationCollections(Request $request)
    {
        $organizationId = $request->get('organizationId');

        $this->collectionService->createOrganizationCollections($organizationId);
    }
}
