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
use App\Services\Amazon\GetUrlResourceService;
use App\Services\CategoryService;
use App\Services\CDNService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Filesystem\FileNotFoundException;


class ResourceAmazonController extends Controller
{
    private SaveAmazonResourceService $saveAmazonResourceService;
    private GetAmazonResourceService $getAmazonResourceService;
    private GetCDNResourceService $getCDNResourceService;
    private CDNService  $cdnService;
    private AssignWorkspaceService $assignWorkspaceService;
    private CategoryService $categoryService;
    private NotificationService $notificationService;
    private GetUrlResourceService $getUrlResourceService;

    public function __construct(SaveAmazonResourceService $saveAmazonResourceService, GetAmazonResourceService $getAmazonResourceService, CDNService $cdnService, GetCDNResourceService $getCDNResourceService, AssignWorkspaceService $assignWorkspaceService, CategoryService $categoryService, NotificationService $notificationService, GetUrlResourceService $getUrlResourceService)
    {
        $this->saveAmazonResourceService = $saveAmazonResourceService;
        $this->getAmazonResourceService = $getAmazonResourceService;
        $this->cdnService = $cdnService;
        $this->getCDNResourceService = $getCDNResourceService;
        $this->assignWorkspaceService = $assignWorkspaceService;
        $this->categoryService = $categoryService;
        $this->notificationService = $notificationService;
        $this->getUrlResourceService = $getUrlResourceService;
    }
    /**
     * Saves a resource on the specified CDN
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $cdnCode
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, $cdnCode): JsonResponse
{
    try {
        $cdn = $this->cdnService->getCDNInfo($cdnCode);
        if ($cdn === null) {
            throw new NotFoundHttpException('The CDN doesn\'t exist.');
        }

        if (!$cdn->isCollectionAccessible($request->collection_id)) {
            throw new AccessDeniedHttpException('The collection isn\'t accessible for this CDN.');
        }

        if (preg_match('/s3.*?.amazonaws\.com/', $request->urlFile)) {
            try {
                $remoteFile = $this->getAmazonResourceService->getResource($request->urlFile);
            } catch (FileNotFoundException $e) {
                throw new NotFoundHttpException('The file doesn\'t exist.');
            }
        }else{
            try {
                $remoteFile = $this->getUrlResourceService->getResource($request->urlFile);
            } catch (NotFoundHttpException $e) {
                throw new NotFoundHttpException('The file doesn\'t exist.');
            }
        }
        
        
        $files['File'] = $remoteFile;

        $collection = Collection::find($request->collection_id);
        if ($collection === null) {
            throw new NotFoundHttpException('The collection doesn\'t exist.');
        }

        $type = $collection->accept;
        $lang = $request->lang ?? false;

        $resource = $this->saveAmazonResourceService->save(
            $request->urlFile,
            $request->nameFile,
            $request->metadata,
            $request->collection_id,
            $type,
            $request->workspace_id,
            $files,
            $lang
        );
       
        if (preg_match('/s3.*?.amazonaws\.com/', $request->urlFile)){
            //$notification = $this->notificationService->notification($request->nameFile,$request->metadata, "url");
           // dd($notification);
        }
        return response()->json(['resource_id' => $resource->id], Response::HTTP_OK);

    } catch (NotFoundHttpException $e) {
        return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
    } catch (AccessDeniedHttpException $e) {
        return response()->json(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error saving resource: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
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
            return  response()->json($resource)
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
            $workspace = Workspace::where('id', $workspaceId)->first();
            if ($workspace === null) {
                throw new NotFoundHttpException('The workspace doesn\'t exist.');
            }

            $category = Category::where('name', $isbn)->first();
            if ($category === null) {
                throw new NotFoundHttpException('The ISBN doesn\'t exist.');
            }

            $resources = $this->categoryService->getResources($category, true);
            $this->assignWorkspaceService->assignWorkspace($workspaceId, $resources);
            return response(['message' => 'Resources assigned to workspace ' . $workspaceId . ' successfully'], Response::HTTP_OK);
        } catch (NotFoundHttpException $e) {
            return response(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
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
            $workspace = Workspace::where('id', $workspaceId)->first();
            if ($workspace === null) {
                throw new NotFoundHttpException('The workspace doesn\'t exist.');
            }

            $category = Category::where('name', $isbn)->first();
            if ($category === null) {
                throw new NotFoundHttpException('The ISBN doesn\'t exist.');
            }

            $resources = $this->categoryService->getResources($category, true);
            $this->assignWorkspaceService->unassignWorkspace($workspaceId, $resources);
            return response(['message' => "Resources with ISBN: $isbn deassigned from workspace $workspaceId successfully"], Response::HTTP_OK);
        } catch (NotFoundHttpException $e) {
            return response(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response(['error' => 'error deassigning resources'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function notification()
    {
       // return response()->json($this->notificationService->notification());
    }
}
