<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class CrawlerJobStatus extends Enum
{
    const Created =   0;
    const Processing =   1;
    const Done = 2;
}
