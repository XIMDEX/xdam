<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class CDNControllerAction extends Enum
{
    const ADD_COLLECTION = "add_collection";
    const REMOVE_COLLECTION = "remove_collection";
    const CHECK_COLLECTION = "check_collection";
    const LIST_COLLECTIONS = "list_collections";
}
