<?php

namespace App\Http\Controllers;

use App\Enums\CollectionType;
use App\Enums\ResourceType;
use App\Http\Requests\GetCatalogueRequest;
use App\Services\Catalogue\CatalogueService;
use Illuminate\Http\JsonResponse;
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


    private function checkCollectionType(string $type): string
    {
        $collection = CollectionType::multimedia;
        if ($type == CollectionType::course) {
            $collection = CollectionType::course;
        }
        return $collection;
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
        $pageParams['search'] = $request->get('search', "");

        $sortParams = [];
        $sortParams['orderType'] = strtolower($request->get('orderType', 'ASC'));
        $sortParams['orderBy'] = $request->get('order_by');
        $sortParams['order'] = $request->get('order');

        $facetsFilter = $request->get('facets', []);

        $response = $this->catalogueService->indexByCollection(
            $pageParams,
            $sortParams,
            $facetsFilter,
            $this->checkCollectionType($request->collection)
        );

        return response()->json($response);
    }

    /**
     * @param GetCatalogueRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function get(GetCatalogueRequest $request)
    {
        return response()->json($this->catalogueService->exploreByCollection($request->collection));
    }

    /**
     * @return JsonResponse
     */
    public function checkSolr(): JsonResponse
    {
        return response()->json($this->catalogueService->checkSolr());
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
