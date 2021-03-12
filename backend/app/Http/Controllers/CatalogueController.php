<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetCatalogueRequest;
use App\Models\Collection;
use App\Services\Catalogue\CatalogueService;
use Symfony\Component\HttpFoundation\Response;

class CatalogueController extends Controller
{
    /**
     * @var CatalogueService
     */
    private CatalogueService $catalogueService;

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
     * @param Collection $collection
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(GetCatalogueRequest $request, Collection $collection)
    {

        $pageParams = [];
        $pageParams['currentPage'] = $request->get('page', 1);
        $pageParams['limit'] = $request->get('limit', 1);
        $pageParams['search'] = $request->get('search', "");

        $sortParams = [];
        $sortParams['orderType'] = strtolower($request->get('orderType', 'ASC'));
        $sortParams['orderBy'] = $request->get('order_by');
        $sortParams['order'] = $request->get('order');

        $facetsFilter = $request->get('facets', []);
        $facetsFilter['collection'] = [(string)$collection->id];

        $response = $this->catalogueService->indexByCollection(
            $pageParams,
            $sortParams,
            $facetsFilter,
            $collection
        );

        return response()->json($response);
    }


}
