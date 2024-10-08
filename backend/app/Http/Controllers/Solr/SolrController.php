<?php

namespace App\Http\Controllers\Solr;

use App\Http\Controllers\Controller;
use App\Services\Catalogue\FacetManager;
use App\Services\Solr\SolrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Solarium\Client;
use Symfony\Component\HttpFoundation\Response;

class SolrController extends Controller
{
    const ACTIONS_ALLOW = ['select', 'update'];
    private $cores_allow = [];
    private $solrService;

    public function __construct(SolrService $solrService, FacetManager $facetManager)
    {
        $this->cores_allow = config('solarium.solr_core_allow_external');
        $this->solrService = $solrService;

        // check if exists each method for actions
        foreach (self::ACTIONS_ALLOW as $action) {
            if (!method_exists($this, $action)) throw new \Error("Method $action for allowed actions not exists");
        }
    }

    public function handle($core, $action, Request $request)
    {
        $method = $request->getMethod();

        if (!in_array($core, $this->cores_allow)) {
            return response(json_encode(['e' => $core, 'cores' => $this->cores_allow]), Response::HTTP_NOT_FOUND);
        }

        if (!in_array($action, self::ACTIONS_ALLOW)) {
            return response('Err2', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($request->get('wt', 'json') !== 'json' ) {
            return response('Err3', Response::HTTP_NOT_ACCEPTABLE);
        }

        $params = [
            'core' => $core,
            'action' => $action
        ];

        $output = $this->handleQuery($params, $request);
        try {
            $json_output = json_decode($output);
            return response()->json($json_output, Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response('Err4', Response::HTTP_FAILED_DEPENDENCY);
        }
    }

    public function handleQuery($params, $request)
    {

        $seach_params = [];
        $array = explode('&', $_SERVER['QUERY_STRING']);
        foreach ($array as $value) {
            $data_value = explode('=', $value);
            if (!isset($seach_params[$data_value[0]])) {
                $seach_params[$data_value[0]] = 1;
            } else {
                $seach_params[$data_value[0]]++;
            }
        }
        foreach ($array as $idx => $value) {
            $data_value = explode('=', $value);
            if ($seach_params[$data_value[0]] > 1) {
                $array[$idx] = str_replace('=', '[]=', $value);
            }
        }

        $queryParams = implode('&', $array);
        parse_str($queryParams, $request_params);
        return $this->{$params['action']}($request_params, $params['core']);
    }

    public function select($params, $core)
    {
        return $this->solrService->handleSelect($params, $core);
    }

    public function update($request, $client)
    {
        $query = $client->createUpdate();
        $facetSet = $query->getFacetSet();

        $searchParams = $query->getQuery();

    }
}
