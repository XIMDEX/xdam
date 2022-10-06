<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetCatalogueRequest;
use App\Models\Collection;
use App\Models\DamResource;
use App\Models\Workspace;
use App\Services\CDNService;
use App\Services\Catalogue\CatalogueService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Solarium\Client;

class CatalogueController extends Controller
{
    /**
     * @var CDNService
     */
    private CDNService $cdnService;

    /**
     * @var CatalogueService
     */
    private CatalogueService $catalogueService;

    /**
     * CatalogueController constructor.
     * @param CDNService $cdnService
     * @param CatalogueService $catalogueService
     */
    public function __construct(CDNService $cdnService, CatalogueService $catalogueService)
    {
        $this->cdnService = $cdnService;
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
        $facetsFilter['organization'] = $collection->organization_id;
        $facetsFilter['collections'] = $collection->id;

        $response = $this->catalogueService->indexByCollection(
            $pageParams,
            $sortParams,
            $facetsFilter,
            $collection
        );

        $cdns = $this->cdnService->getCDNsAttachedToCollection($collection);

        usort($cdns, function ($item1, $item2) {
            return $item2['id'] < $item1['id'];
        });

        for ($i = 0; $i < count($response->data); $i++) {
            $resource = DamResource::where('id', $response->data[$i]['id'])->first();

            if ($resource !== null) {
                $response->data[$i]['data']->max_files = $resource->collection->getMaxNumberOfFiles();

                foreach ($cdns as $currentCDN) {
                    $auxCDN = clone $currentCDN;
                    $auxCDN->setHash($this->cdnService->generateDamResourceHash($auxCDN, $resource, $collection->id));
                    if ($auxCDN->getHash() !== null) $response->data[$i]['data']->cdns_attached[] = $auxCDN;
                }
            }
        }

        return response()->json($response);
    }

    public function getCatalogueByWorkspace(Request $request, Workspace $workspace)
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
        $facetsFilter['workspaces'] = $workspace->id;

        $response = $this->catalogueService->indexByWorkspace(
            $pageParams,
            $sortParams,
            $facetsFilter,
            $workspace
        );

        return response()->json($response);
    }
}
