<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static document()
 * @method static static video()
 * @method static static image()
 * @method static static audio()
 * @method static static url()
 * @method static static course()
 * @method static static assessment()
 * @method static static activity()
 * @method static static book()
 */

final class ResourceType extends Enum
{
    const document = "document";
    const video = "video";
    const image = "image";
    const audio = "audio";
    const url = "url";
    const multimedia = "multimedia";
    const course = "course";
    const assessment = "assessment";
    const activity = "activity";
    const book = "book";
}
