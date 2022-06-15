<?php
declare(strict_types=1);

namespace App\Exceptions\Workspace;

use \Exception;

class TooManyWorkspaces extends Exception
{
    public function __construct($workspaceName, $code = 0, Exception $previous = null)
    {
        parent::__construct(
            "There are to many workspace for $workspaceName",
            $code,
            $previous
        );
    }
}
