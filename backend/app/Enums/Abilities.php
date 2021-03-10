<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Abilities extends Enum
{
    //"FOLDERS" Organizations / Workspaces
    const UPDATE_WORKSPACE = "UPDATE_WORKSPACE";
    const READ_WORKSPACE = "READ_WORKSPACE";
    const DELETE_WORKSPACE = "DELETE_WORKSPACE";
    const CREATE_WORKSPACE = "CREATE_WORKSPAE";

    const MANAGE_ROLES = "MANAGE_ROLES";
    const MANAGE_ORGANIZATION = "MANAGE_ORGANIZATION";
    const MANAGE_WORKSPACE = "MANAGE_WORKSPACE";

    //"FILES" Resources
    const CREATE_RESOURCE = "CREATE_RESOURCE"; //(Create)
    const READ_RESOURCE = "READ_RESOURCE"; //(Read)
    const READ_RESOURCE_CARD = "READ_RESOURCE_REPORT"; //(Read)
    const DOWNLOAD_RESOURCE = "DOWNLOAD_RESORCE"; //(Read)

    const UPDATE_RESOURCE = "UPDATE_RESOURCE"; //(Update)
    const UPDATE_RESOURCE_CARD = "UPDATE_RESOURCE_CARD"; //(Update)

    const REMOVE_RESOURCE = "REMOVE_RESOURCE"; //(Delete)
    const REMOVE_RESOURCE_CARD = "REMOVE_RESOURCE_CARD"; //(Delete)

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
