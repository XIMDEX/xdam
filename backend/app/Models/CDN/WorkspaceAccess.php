<?php

namespace App\Models\CDN;

use App\Models\CDN\DefaultCDNAccess;

class WorkspaceAccess extends DefaultCDNAccess
{
    
    public function areRequirementsMet($params)
    {
        // $data_token [damResourceHash, workspaceID, areaID, isDownloadble]
        // data_resource [workspaces, categories]
        $params = array_merge(['data_token' => null, 'data_resource' => null], $params);
        list('data_token' => $data_token, 'data_resource' => $data_resource) = $params;
       
        if (!$data_token || !$data_resource) return false;

        foreach($data_resource['workspaces'] as $wsp) {
            if ($this->checkWorkspaces($wsp->id, $data_token['workspaceID'])) {
                return true;
            }
        }
        return false; 
    }

    private function checkWorkspaces($rule, $workspace)
    {
        return $rule == $workspace;
    }
}