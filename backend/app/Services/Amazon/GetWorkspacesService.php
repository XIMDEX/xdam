<?php 

namespace App\Services\Amazon;

use App\Services\CDNService;


class GetWorkspacesService
{
    private $cdnService;
    public function __construct( CDNService $cdnService)
    {
        $this->cdnService = $cdnService;
    }

    public function getWorkspaces($damResourceHash)
    {
        $data = $this->cdnService->decodeHash($damResourceHash);
        $damResourceHash = $data['damResourceHash'];

        $resource = $this->cdnService->getAttachedDamResource($damResourceHash);
    
        return $resource->workspaces()->get();
    }

    


}