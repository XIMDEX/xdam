<?php

namespace App\Http\Controllers;

use App\Services\Dam\DamService;
use App\Services\Dam\DamServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\File;
use League\Flysystem\FileNotFoundException;

class ResourceController extends Controller
{

    /**
     * Main route for dam; pagination, search, facets, filters ...
     */
    public function index(Request $request, DamServiceInterface $damService)
    {
        $solarium = $damService->getSolarium();
        $currentPage = $request->get('page', 1);
        $limit = $request->get('limit', 1);
        $search = $request->get('search', 1);

        /*
         * TODO: Check unused parameters, because them has been copied from original request
         */
        $default = $request->get('default', 1);
        $orderField = $request->get('orderField', null);
        $orderType = strtolower($request->get('orderType', 'ASC'));
        $orderBy = $request->get('order_by');
        $order =  $request->get('order');

        $facetsFilter = $request->get('facets');

        $query = $solarium->createSelect();

        $facetSet = $query->getFacetSet();

        /* the facets to be applied to the query  */
        $damService->setFacets($facetSet, $facetsFilter);

        /*  limit the query to facets that the user has marked us */
        $damService->setQueryByFacets($query, $facetsFilter);

        /* if we have a search param, restrict the query */
        if(!empty($search)) {
            $query->setQuery("name:*" . $search . "*");
        }

        $allDocuments = $solarium->select($query);
        $documentsFound = $allDocuments->getNumFound();
        $faceSetFound = $allDocuments->getFacetSet();

        $totalPages = ceil($documentsFound / $limit);
        $currentPageFrom = ($currentPage - 1) * $limit;

        /* Limit query by pagination limits */
        $query->setStart($currentPageFrom)->setRows($limit);

        $allDocuments = $solarium->execute($query);

        $documentsResponse = [];

        foreach ($allDocuments as $document) {
            /*
             * TODO: Check item dependency currently is not in use
             */
            $fields = $document->getFields();
            $fields["items"] = ["A que sabe la luna"];
            $fields["resource_id"] = $fields["id"];
            $fields["resource_context"] = $fields["id"];
            $documentsResponse[] = $fields;
        }

        /* Response with pagination data */
        $response = new \stdClass();
        $response->facets = $damService->getFacets($faceSetFound, $facetsFilter);
        $response->current_page= $currentPage;
        $response->data = $documentsResponse;
        $response->per_page = $limit;
        $response->last_page = $totalPages;
        $response->next_page = (($currentPage + 1) > $totalPages) ? $totalPages : $currentPage + 1;
        $response->prev_page = (($currentPage - 1) > 1) ? $currentPage - 1 : 1;
        $response->highlighting = [];
        $response->total = $documentsFound;

        return $this->response($response);
    }

    public function store(Request $request)
    {
        /*
          * TODO: save a resource that does not exist on the server
          */
    }

    public function update(Request $request, string $id)
    {
        /*
          * TODO: if send us a set of folders, we should know how to manage it
          */
        $params = $request->all();

        $file = $request->file('resource');

        $originalName = $file->getClientOriginalName();

        $mainFs = config('app.input_files_dir', '');

        if (file_exists($mainFs)) {
            // if we have the inputs directory, we move the uploaded file there
            if (array_key_exists('title', $params)) {
                $originalName = $params["title"];
            }
            $file->move($mainFs, $originalName);
            $uploadedPath = $mainFs . DIRECTORY_SEPARATOR . $originalName;
        }

        try {
            $resource = new File();
            // update the resource with the data that comes from the request
            foreach($params as $keyParam => $valueParam) {
                if ( property_exists($resource, $keyParam)) {
                    $resource->$keyParam = $valueParam;
                }
            }

            $resource->save();
            $response = $this->response(['data' => $resource], null, 201);
        } catch (ModelNotFoundException $ex) {
            $response = $this->response(null, 'Resource not found', 404);
        } catch (\Exception $ex) {
            $response = $this->response(null, $ex->getMessage(), 500);
        }


        return $response;
    }

    /**
     * Display the specified resource by id.
     **/
    public function show( Request $request, string $id )
    {
        $response = new \stdClass();
        $response->errors = null;

        $resource =  File::where('id', $id)->first();

        $resource->previews = $resource->preview()->first();
        $response->result = new \stdClass();
        $response->result->data = $resource;
        return response()->json($response);
    }

    /**
     * Get thumbnail for the specified resource.
     * @param string $id
     */
    public function image(string $id)
    {
        // TODO add support to different sizes
        try {
            //$size = Request::get('size', '');

            $resource =  File::where('id', $id)->first();

            $image = $resource->getThumbnail();

            if ($image && file_exists($image->local_path)) {
                $response = response()->file($image->local_path);
            } else {
                throw FileNotFoundException;
            }
        } catch (ModelNotFoundException $e) {
            $response = $this->response(null, 'Resource not found', 404);
        } catch (FileNotFoundException $ex) {
            $response = $this->response(null, 'Thumbnail resource not found', 404);
        }

        return $response;
    }

    /**
     * Get the specified resource by id.
     **/
    public function downloadFile(string $id)
    {
        // TODO rewrite method, code has been copied from xdam-laravel
        try {
            $resource =  File::where('id', $id)->first();

            if (file_exists($resource->dam_path)) {
                $response = response()->file($resource->dam_path);
            } else {
                throw FileNotFoundException;
            }
        } catch (FileNotFoundException $e) {
            $response = $this->response(null, 'File not found', 404);
        } catch (ModelNotFoundException $e) {
            $response = $this->response(null, 'Resource not found', 404);
        }

        return $response;
    }

    /**
     * Delete specific resource by id
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $id)
    {
        try {
            $model =  File::where('id', $id)->first();

            if ( null == $model) {
                throw new ModelNotFoundException;
            }

            // cascade deletion already takes care of deleting all dependencies
            $delete = $model->delete();

            $response = $this->response(null, null, $delete ? 200 : 500);

        } catch (ModelNotFoundException $ex) {

            $response = $this->response(null, "Resource not found", 404);
        }
        return $response;
    }
}
