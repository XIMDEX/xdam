<?php

namespace App\Utils;

use App\Enums\Abilities;
use App\Models\DamResource;

class PermissionCalc
{

    public static function check($request, $user, $ability)
    {
        if(isset($request->damUrl)) {
            //Get te DamResource based on damUrl
            return false;
        }

        //get all workspaces where the resource is
        $workspaces_where_resource_is = $request->damResource->workspaces()->get();

        //now get all abilities that the user has in each workspace
        $user_abilities_in_workspaces = [];
        foreach ($workspaces_where_resource_is as $wsp) {
            foreach ($user->abilitiesOnEntity($wsp->id, Workspace::class) as $abilities) {
                $user_abilities_in_workspaces[] = $abilities->toArray();
            }
        }

        //then authorize if any of these abilities match with ShowResource
        //this means that the user has te READ_RESOURCE ability in this or some other workspace where the resource is attached.
        //Also works if resource is attached in the public workspace
        foreach ($user_abilities_in_workspaces as $ability) {
            if($ability['name'] == $ability) {
                return true;
            }
        }
    }
}
