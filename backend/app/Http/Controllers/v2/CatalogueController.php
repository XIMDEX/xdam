<?php

namespace App\Http\Controllers\v2;

use App\Http\Requests\GetCatalogueRequest;
use App\Models\Collection;
use App\Services\Catalogue\CatalogueService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

final class CatalogueController extends Controller
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

    public function index(Request $request, Collection $collection)
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
        $facetsFilter['organization'] = $collection->organization_id;
        
        $response = $this->catalogueService->indexByCollection(
            $pageParams,
            $sortParams,
            $facetsFilter,
            $collection
        );

        return response()->json($response);
    }
}
