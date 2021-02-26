<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Abilities extends Enum
{
    const UpdateWorkspace = "update-workspace";
    const ViewWorkspace = "view-workspace";
    const DeleteWorkspace = "delete-workspace";

    const ManageRoles = "manage-roles";
    const ManageOrganization = "manage-organizations";
    const ManageWorkspace = "manage-workspaces";

    //RESOURCES

    const CreateNewResource = "create-new-resource"; //(Create)
    const ShowResource = "show-resources"; //(Read)
    const DownloadResource = "download-resource"; //(Read)
    const ReadResourceReport = "read-resource-report"; //(Read)
    const EditResource = "edit-resource"; //(Update)
    const EditResourceReport = "edit-resource-report"; //(Update)
    const RemoveResource = "remove-resource"; //(Delete)

}
