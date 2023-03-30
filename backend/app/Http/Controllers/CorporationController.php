<?php

namespace App\Http\Controllers;

use App\Http\Resources\CorporationCollection;
use App\Http\Resources\CorporationResource;
use App\Http\Resources\ResourceCollection;
use App\Models\Corporation;
use App\Services\CorporationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorporationController extends Controller
{
    /**
     * @var CorporationService
     */
    private $corporationService;

    /**
     * CorporationService constructor.
     * @param CorporationService $corporationService
     */
    public function __construct(CorporationService $corporationService)
    {
        $this->corporationService = $corporationService;
    }


    /**
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getAll()
    {
       $categories =  $this->corporationService->getAll();
       return (new CorporationCollection($categories))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param Corporation $corporation
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function get(Corporation $corporation)
    {
        $corporation =  $this->corporationService->get($corporation);
        return (new CorporationResource($corporation))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param Corporation $corporation
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function getResources(Request $request, Corporation $corporation)
    {
        $resources =  $this->corporationService->getResources($corporation, $request->active);
        return (new ResourceCollection($resources))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param Corporation $corporation
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(Corporation $corporation, Request $request)
    {
        $corporation =  $this->corporationService->update($corporation, $request->all());
        return (new CorporationResource($corporation))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function store(Request $request)
    {
        $corporation = $this->corporationService->store($request->all());
        return (new CorporationResource($corporation))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * @param Corporation $corporation
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Corporation $corporation)
    {
        $this->corporationService->delete($corporation);
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
