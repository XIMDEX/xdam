<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ResourceType;

class AssessmentService extends BaseService
{
    public function __construct() {
        parent::__construct();
        self::$type_service = ResourceType::activity;
    }
}
