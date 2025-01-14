<?php

namespace Lib\Xrole\Contracts;

interface PermissionServiceInterface {
    public function hasPermission($permission);
    public function canSearch();
    public function canRead();
    public function canCreate();
    public function canUpdate();
    public function canRemove();
    public function canOperate();
    public function isAdmin();
    public function isSuperAdmin();
}