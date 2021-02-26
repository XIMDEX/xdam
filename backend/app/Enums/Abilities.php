<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Abilities extends Enum
{
    //"FOLDERS" Organizations / Workspaces
    const UpdateWorkspace = "update-workspace";
    const ViewWorkspace = "view-workspace";
    const DeleteWorkspace = "delete-workspace";

    const ManageRoles = "manage-roles";
    const ManageOrganization = "manage-organizations";
    const ManageWorkspace = "manage-workspaces";

    //"FILES" Resources
    const CreateNewResource = "create-new-resource"; //(Create)
    const ShowResource = "show-resources"; //(Read)
    const DownloadResource = "download-resource"; //(Read)
    const ReadResourceReport = "read-resource-report"; //(Read)
    const EditResource = "edit-resource"; //(Update)
    const EditResourceReport = "edit-resource-report"; //(Update)
    const RemoveResource = "remove-resource"; //(Delete)

    public static function resourceManagerAbilities()
    {
        return [
            self::ShowResource,
            self::DownloadResource,
            self::ReadResourceReport,
            self::EditResource,
            self::EditResourceReport,
            self::RemoveResource,
        ];
    }

    public static function resourceEditorAbilities()
    {
        return [
            self::ShowResource,
            self::ReadResourceReport,
            self::EditResource,
            self::EditResourceReport,

        ];
    }

    public static function resourceReaderAbilities()
    {
        return [
            self::ShowResource,
            self::DownloadResource,
        ];
    }
}
