<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static File()
 * @method static static Preview()
 */
final class MediaType extends Enum
{
    const File =   "file";
    const Preview =   "preview";
}
