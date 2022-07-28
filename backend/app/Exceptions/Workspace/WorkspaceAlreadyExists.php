<?php

declare(strict_types=1);

namespace App\Exceptions\Workspace;

use App\Enums\WorkspaceType;
use \Exception;

class WorkspaceAlreadyExists extends Exception
{
    public function __construct(string $workspaceName, int $organizationId, WorkspaceType $type, $code = 0, Exception $previous = null)
    {
        parent::__construct(
            "A workspace with $workspaceName for the organization with ID: $organizationId and type $type already exists",
            $code,
            $previous
        );
    }
}
