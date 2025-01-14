<?php

namespace Lib\Xrole\Contracts;

interface PermissionsInterface {
    public function hasPermission($permission);
}