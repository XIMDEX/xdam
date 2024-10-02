<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ResourceType;

class AssessmentService extends BaseService
{
    public function __construct() {
        parent::__construct();
        self::$array = ['workspaces' => 'Workspace'];
        self::$type_service = ResourceType::activity;
    }

    public static function handleFacetCard($facets)
    {
        $facets = parent::handleFacetCard($facets);
        $facets = self::addWorkspace(ResourceType::assessment,$facets,array_keys(self::$array));

        return $facets;
    }
}
