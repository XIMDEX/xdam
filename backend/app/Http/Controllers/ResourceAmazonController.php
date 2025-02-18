<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Workspace;
use App\Models\Collection;
use App\Services\Amazon\GetAmazonResourceService;
use App\Services\Amazon\GetCDNResourceService;
use App\Services\Amazon\SaveAmazonResourceService;
use App\Services\Amazon\AssignWorkspaceService;
use App\Services\Amazon\NotificationService;
use App\Services\CategoryService;
use App\Services\CDNService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ResourceAmazonController extends Controller
{
    private SaveAmazonResourceService $saveAmazonResourceService;
    private GetAmazonResourceService $getAmazonResourceService;
    private GetCDNResourceService $getCDNResourceService;
    private CDNService  $cdnService;
    private AssignWorkspaceService $assignWorkspaceService;
    private CategoryService $categoryService;
    private NotificationService $notificationService;

    public function __construct(SaveAmazonResourceService $saveAmazonResourceService, GetAmazonResourceService $getAmazonResourceService, CDNService $cdnService, GetCDNResourceService $getCDNResourceService, AssignWorkspaceService $assignWorkspaceService, CategoryService $categoryService, NotificationService $notificationService)
    {
        $this->saveAmazonResourceService = $saveAmazonResourceService;
        $this->getAmazonResourceService = $getAmazonResourceService;
        $this->cdnService = $cdnService;
        $this->getCDNResourceService = $getCDNResourceService;
        $this->assignWorkspaceService = $assignWorkspaceService;
        $this->categoryService = $categoryService;
        $this->notificationService = $notificationService;
    }
    /**
     * Saves a resource on the specified CDN
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $cdnCode
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, $cdnCode)
    {
        try {
            if (($cdn = $this->cdnService->getCDNInfo($cdnCode)) === null) throw new \Exception('The CDN doesn\'t exist.');
            if ($cdn->isCollectionAccessible($request->collection_id) === false) throw new \Exception('The collection isn\'t accessible for this CDN.');
            
            //$metadataString = ($request->metadata);

            //$tmpFilePath = tempnam(sys_get_temp_dir(), 'metadata_') . '.txt';

            //file_put_contents($tmpFilePath, $metadataString);
            $remoteFile = $this->getAmazonResourceService->getResourceByCurl($request->urlFile);
            $files['File'] = $remoteFile;
            $type = Collection::find($request->collection_id)->accept;
            $lang  = $request->lang ?? false;
            $resource = ($this->saveAmazonResourceService->save($request->urlFile, $request->nameFile, $request->metadata, $request->collection_id,$type, $request->workspace_id,  $files, $lang));
          //  $resource->addMedia($tmpFilePath)->toMediaCollection('File');
            

            $url =  $resource->id;
            return response(['resource_id' => $url])
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['error' => 'error saving resource'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Gets the urls of a resource on the specified CDN
     *
     * @param  string  $cdnCode
     * @param  string  $damResource
     * @return \Illuminate\Http\Response
     */
    public function getUrls($cdnCode, $damResource)
    {
        try {
            $resource = $this->getCDNResourceService->getResourceUrls($cdnCode, $damResource);
            return  response($resource)
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Gets the information of a resource on the specified CDN
     *
     * @param  string  $cdnCode
     * @param  string  $damResource
     * @return \Illuminate\Http\Response
     */
    public function getResource($cdnCode, $damResource)
    {
        try {
            $resource = $this->getCDNResourceService->getResourceInfo($cdnCode, $damResource);
            return  response($resource)
                ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['error' => 'error getting resource'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Returns all the workspaces
     *
     * @return \Illuminate\Http\Response
     */
    public function getWorkspaces()
    {
        try {
            return Workspace::all();
        } catch (\Exception $e) {
            return response(['error' => 'error getting workspaces'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Assigns the resources of the category $isbn to the workspace $workspaceId
     *
     * @param string $isbn
     * @param int $workspaceId
     * @return \Illuminate\Http\Response
     */
    public function assignWorkspace($isbn, $workspaceId)
    {
        try {
            $resources = $this->categoryService->getResources(Category::where('name', $isbn)->first(), true);
            $this->assignWorkspaceService->assignWorkspace($workspaceId, $resources);
            return response(['message' => 'Resources assigned to workspace ' . $workspaceId . ' successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['error' => 'error assigning resources'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Unassigns the resources of the category $isbn from the workspace $workspaceId
     * 
     * @param int $workspaceId
     * @param string $damResource
     * @return \Illuminate\Http\Response
     */
    public function unassignWorkspace($isbn, $workspaceId)
    {
        try {
            $resources = $this->categoryService->getResources(Category::where('name', $isbn)->first(), true);
            $this->assignWorkspaceService->unassignWorkspace($workspaceId,$resources );
            return response(['message' => "Resources with ISBN: $isbn deassigned from workspace $workspaceId successfully"], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response(['error' => 'error deassigning resources'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function notification(){
        return $this->notificationService->notification();
    }
}

