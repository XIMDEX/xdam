<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetCatalogueRequest;
use App\Models\Collection;
use App\Models\DamResource;
use App\Models\Workspace;
use App\Services\CDNService;
use App\Services\Catalogue\CatalogueService;
use App\Services\Catalogue\FacetManager;
use App\Services\CategoryService;
use App\Utils\Texts;
use App\Utils\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Solarium\Client;
use App\Enums\AccessPermission;


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
     * @var CategoryService
     */
    private CategoryService $categoryService;

    /**
     * CatalogueController constructor.
     * @param CDNService $cdnService
     * @param CatalogueService $catalogueService
     */
    public function __construct(CDNService $cdnService, CatalogueService $catalogueService, CategoryService $categoryService)
    {
        $this->cdnService = $cdnService;
        $this->catalogueService = $catalogueService;
        $this->categoryService = $categoryService;
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
        $language = $request->get('lang', env('DEFAULT_LANGUAGE'));

        foreach ($facetsFilter as $facetKey => $values) {
            if (!in_array($facetKey, FacetManager::RADIO_FACETS)) {
                continue;
            }
            
            if (is_array($values)) {
                foreach ($values as $idx_value => $value) {
                    if (strtolower($value) == 'all') {
                        unset($facetsFilter[$facetKey]);
                    } else if (!in_array(strtolower($value), ['true', 'false'])) {
                        $facetsFilter[$facetKey][$idx_value] = Texts::web(strtolower($value), $language);
                    }
                }
            } else {
                if (strtolower($values) == 'all') {
                    unset($facetsFilter[$facetKey]);
                } else if (!in_array(strtolower($values), ['true', 'false'])) {
                    $facetsFilter[$facetKey] = Texts::web(strtolower($values), $language);
                }
            }
        }
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
        $response = $this->appendAllCategories($response, $collection);
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
        $language = $request->get('lang', env('DEFAULT_LANGUAGE'));

        foreach ($facetsFilter as $facetKey => $values) {
            if (!in_array($facetKey, FacetManager::RADIO_FACETS)) {
                continue;
            }
            
            if (is_array($values)) {
                foreach ($values as $idx_value => $value) {
                    if (strtolower($value) == 'all') {
                        unset($facetsFilter[$facetKey]);
                    } else if (!in_array(strtolower($value), ['true', 'false'])) {
                        $facetsFilter[$facetKey][$idx_value] = Texts::web(strtolower($value), $language);
                    }
                }
            } else {
                if (strtolower($values) == 'all') {
                    unset($facetsFilter[$facetKey]);
                } else if (!in_array(strtolower($values), ['true', 'false'])) {
                    $facetsFilter[$facetKey] = Texts::web(strtolower($values), $language);
                }
            }
        }
        // $facetsFilter['organization'] = $collection->organization_id;

        // $facetsFilter['collections'] = $collection->id;
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
                    $hash = $this->cdnService->generateDamResourceHash($auxCDN, $resource, $collection->id);
                    $hasWorkspaceAccessPermission = $this->cdnService->hasAccessPersmission(AccessPermission::workspace, $currentCDN->id);
                    
                    if ($hasWorkspaceAccessPermission) {
                        $workspaces = $resource->workspaces()->get();
                        $categories = $resource->categories()->get();
                        $isDownloadble = false;
                        $areaIDs = [
                            ['id' => 0, 'label' => 'Accesible all center']
                        ];
                        $tokens = [];
                        foreach ($workspaces as $wsp) {
                            foreach ($areaIDs as $area) {
                                if ($area['id'] == 0) {
                                    $tokens["$wsp->name ($wsp->id)"] = $this->cdnService->encodeHash($hash,$wsp->id, $area['id'], $isDownloadble);
                                } else {
                                    $label_area = 'area'.$area['id'];
                                    if (!isset($tokens[$label_area])) $tokens[$label_area] = [];
                                    $tokens[$label_area][] = $this->cdnService->encodeHash($hash,$wsp->id, $area['id'], $isDownloadble);
                                }

                            }
                        }
                        $auxCDN->setHash($tokens);  
                        if ($auxCDN->getHash() !== null) $response->data[$i]['data']->cdns_attached[] = $auxCDN;
                    } else {
                        $auxCDN->setHash($hash);
                        if ($auxCDN->getHash() !== null) $response->data[$i]['data']->cdns_attached[] = $auxCDN;
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Add all categories to catalogue response
     * @param object $response
     * @return object
     */
    private function appendAllCategories(object $response, $collection)
    {
        $categories = [];
        $all_categories = $this->categoryService->getAll();
        $type = $collection->solr_connection;
        $multimedia_types = ['image', 'audio', 'video'];

        foreach ($all_categories as $category) {
            //* For others use
                // if ($type !== 'multimedia' && $category->type == $type) {
                //     $categories[] = $category;
                // }
                // if ($type === 'multimedia' && in_array($category->type, $multimedia_types) ) {
                //     $categories[] = $category;
                // }
            //* For the moment only use on course
            if ($type === 'course' && $category->type == $type) {
                $categories[] = $category;
            }
        }

        foreach ($response->facets as $index => $facet) {
            if ($facet['key'] === 'categories') {
                $values = array_keys($facet['values']);
                foreach ($categories as $cateogory) {
                    $key_category = strtolower($cateogory->name);
                    if (!in_array($key_category, $values)) {
                        $response->facets[$index]['values'][$key_category] = ['count' => 0, 'selected' => false, 'radio' => false];
                    }
                }
                break;
            }
        }

        return $response;
    }
}
