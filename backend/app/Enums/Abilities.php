<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Abilities extends Enum
{
    //Workspace
    const UPDATE_WORKSPACE = "update-workspace";
    const READ_WORKSPACE = "read-workspace";
    const DELETE_WORKSPACE = "delete-workspace";
    const CREATE_WORKSPACE = "create-workspace";
    const MANAGE_WORKSPACE = "manage-workspace";

    //organization
    const MANAGE_ROLES = "manage-roles";
    const MANAGE_ORGANIZATION = "manage-organization";
    const MANAGE_ORGANIZATION_WORKSPACES = "manage-organization-workspaces";
    const BASIC_ORGANIZATION_USER = "basic-organization-user";

    //Resources
    const CREATE_RESOURCE = "create-resource"; //(Create)
    const READ_RESOURCE = "read-resource"; //(Read)
    const READ_RESOURCE_CARD = "read-resource-card"; //(Read)
    const DOWNLOAD_RESOURCE = "download-resource"; //(Read)

    const UPDATE_RESOURCE = "update-resource"; //(Update)
    const UPDATE_RESOURCE_CARD = "update-resource-card"; //(Update)

    const REMOVE_RESOURCE = "remove-resource"; //(Delete)
    const REMOVE_RESOURCE_CARD = "remove-resource-card"; //(Delete)

    public static function resourceManagerAbilities()
    {
        return [
            self::CREATE_RESOURCE,
            self::READ_RESOURCE,
            self::DOWNLOAD_RESOURCE,
            self::READ_RESOURCE_CARD,
            self::UPDATE_RESOURCE,
            self::UPDATE_RESOURCE_CARD,
            self::REMOVE_RESOURCE,
            self::REMOVE_RESOURCE_CARD,
        ];
    }

    public static function resourceEditorAbilities()
    {
        return [
            self::READ_RESOURCE,
            self::READ_RESOURCE_CARD,
            self::UPDATE_RESOURCE,
            self::UPDATE_RESOURCE_CARD,
            self::REMOVE_RESOURCE_CARD,
        ];
    }

    public static function resourceReaderAbilities()
    {
        return [
            self::READ_RESOURCE,
            self::DOWNLOAD_RESOURCE,
        ];
    }
}
