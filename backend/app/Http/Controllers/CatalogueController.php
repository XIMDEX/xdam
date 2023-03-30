<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetCatalogueRequest;
use App\Models\Collection;
use App\Models\DamResource;
use App\Models\Workspace;
use App\Services\CDNService;
use App\Services\Catalogue\CatalogueService;
use App\Utils\Utils;
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

        $response = $this->formatLOMFacetsResponse($response);
        $response = $this->appendCDNDataToCatalogueResponse($response, $collection);
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

    /**
     * Formats the LOM/LOMES facet response
     * @param object $response
     * @return object
     */
    private function formatLOMFacetsResponse(object $response)
    {
        $solrFacetsConfig = config('solr_facets', []);
        $lomConfig = (array_key_exists('lom', $solrFacetsConfig) ? $solrFacetsConfig['lom'] : null);
        $lomesConfig = (array_key_exists('lomes', $solrFacetsConfig) ? $solrFacetsConfig['lomes'] : null);
        $constantsConfig = (array_key_exists('constants', $solrFacetsConfig) ? $solrFacetsConfig['constants'] : null);
        $lomSchema = Utils::getLomSchema(true);
        $lomesSchema = Utils::getLomesSchema(true);

        if ($constantsConfig !== null && $lomConfig !== null && $lomesConfig !== null) {
            $lomPos = $lomesPos = -1;
            $lomFacet = $lomesFacet = null;

            foreach ($response->facets as $key => $facet) {
                if ($facet['key'] === 'lom') {
                    $lomPos = $key;
                    $lomFacet = $facet;
                } elseif ($facet['key'] === 'lomes') {
                    $lomesPos = $key;
                    $lomesFacet = $facet;
                }
            }

            if ($lomPos !== -1 && $lomFacet !== null) {
                $lomFacet = $this->formatLOMFacet($lomConfig, $constantsConfig, $lomFacet, $lomSchema);
                $response->facets[$lomPos] = $lomFacet;
            }

            if ($lomesPos !== -1 && $lomesFacet !== null) {
                $lomesFacet = $this->formatLOMFacet($lomesConfig, $constantsConfig, $lomesFacet, $lomesSchema);
                $response->facets[$lomesPos] = $lomesFacet;
            }
        }

        return $response;
    }

    private function formatLOMFacet(array $config, array $constants, array $facet, array $schema)
    {
        $spCh = $constants['special_character'];
        $keySeparator = Utils::getRepetitiveString($spCh, $constants['key_separator']);
        $valueSeparator = Utils::getRepetitiveString($spCh, $constants['value_separator']);
        $chMap = $constants['characters_map'];

        usort($chMap, function ($item1, $item2) {
            return $item2['to'] > $item1['to'];
        });

        foreach ($facet['values'] as $key => $value) {
            $auxKey = $key;
            $keyObject = [
                'o_key'     => $key,
                'key'       => null,
                'key_title' => null,
                'subkey'    => null,
                'value'     => null
            ];

            foreach ($chMap as $chItem) {
                $auxCharacter = Utils::getRepetitiveString($spCh, $chItem['to']);
                $auxKey = str_replace($auxCharacter, $chItem['from'], $auxKey);
            }

            // Separates the key and the value
            $auxSplit = explode($valueSeparator, $auxKey);
            $keyObject['value'] = $auxSplit[1];
            $auxKey = $auxSplit[0];

            // Separates the key and the subkey
            $auxSplit = explode($keySeparator, $auxKey);
            $keyObject['key'] = $auxSplit[0];
            if (count($auxSplit) > 1) $keyObject['subkey'] = $auxSplit[1];

            // Gets the original key and subkey, by its aliases
            foreach ($config as $cItem) {
                $found = (
                    $cItem['key_alias'] === $keyObject['key']
                        || $cItem['key'] === $keyObject['key']
                );

                if ($cItem['subkey'] !== null) {
                    $found = (
                        $found
                            && (
                                $cItem['subkey_alias'] === $keyObject['subkey']
                                    || $cItem['subkey'] === $keyObject['subkey']
                            )
                    );
                }

                if ($found) {
                    $keyObject['key'] = $cItem['key'];
                    $keyObject['subkey'] = $cItem['subkey'];
                }
            }

            // Gets the key title
            foreach ($schema['tabs'] as $tab) {
                foreach ($tab['properties'] as $pItem) {
                    if ($pItem['data_field'] === $keyObject['key']) {
                        $keyObject['key_title'] = $pItem['title'];
                    }
                }
            }

            // Appends the key info
            $facet['values'][$key]['key'] = $keyObject;
        }

        return $facet;
    }

    /**
     * Appends the CDN data to catalogue response
     * @param object $response
     * @param Collection $collection
     * @return object
     */
    private function appendCDNDataToCatalogueResponse(object $response, Collection $collection)
    {
        $cdns = $this->cdnService->getCDNsAttachedToCollection($collection);

        usort($cdns, function ($item1, $item2) {
            return $item2['id'] < $item1['id'];
        });

        for ($i = 0; $i < count($response->data); $i++) {
            $resource = DamResource::where('id', $response->data[$i]['id'])->first();

            if ($resource !== null) {
                $response->data[$i]['data']->max_files = $resource->collection->getMaxNumberOfFiles();
                $response->data[$i]['data']->cdns_attached = [];

                for ($j = 0; $j < count($cdns); $j++) {
                    $currentCDN = $cdns[$j];
                    $auxCDN = clone $currentCDN;
                    $auxCDN->setHash($this->cdnService->generateDamResourceHash($auxCDN, $resource, $collection->id));
                    if ($auxCDN->getHash() !== null) $response->data[$i]['data']->cdns_attached[] = $auxCDN;
                }
            }
        }

        return $response;
    }
}
