<?php

namespace App\Http\Controllers;

use App\Enums\ResourceType;
use App\Http\Requests\GetCatalogueRequest;
use App\Services\Catalogue\CatalogueService;
use Symfony\Component\HttpFoundation\Response;

class CatalogueController extends Controller
{
    /**
     * @var CatalogueService
     */
    private $catalogueService;

    /**
     * CatalogueController constructor.
     * @param CatalogueService $catalogueService
     */
    public function __construct(CatalogueService $catalogueService)
    {
        $this->catalogueService = $catalogueService;
    }

    /**
     * @param GetCatalogueRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function index(GetCatalogueRequest $request)
    {
        $pageParams = [];
        $pageParams['currentPage'] = $request->get('page', 1);
        $pageParams['limit'] = $request->get('limit', 1);
        $pageParams['search'] = $request->get('search', 1);

        $sortParams = [];
        $sortParams['orderType'] = strtolower($request->get('orderType', 'ASC'));
        $sortParams['orderBy'] = $request->get('order_by');
        $sortParams['order'] = $request->get('order');

        $facetsFilter = $request->get('facets');
        $facetsFilter["type"] = ResourceType::fromKey($request->type)->key;
        $response = $this->catalogueService->indexByType($pageParams, $sortParams, $facetsFilter);

        return response()->json($response);
    }

    /**
     * @param GetCatalogueRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function get(GetCatalogueRequest $request)
    {
        return response()->json($this->catalogueService->exploreByType(ResourceType::fromKey($request->type)));
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function delete()
    {
        $this->catalogueService->resetIndex();
        return response(null, Response::HTTP_NO_CONTENT);
    }

}
