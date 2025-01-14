<?php

namespace Lib\Xrole\Contracts;

interface PermissionConstants {
    const SEARCH      = 0b10000000;
    const READ        = 0b01000000;
    const CREATE      = 0b00100000;
    const UPDATE      = 0b00010000;
    const REMOVE      = 0b00001000;
    const OPERATE     = 0b00000100;
    const ADMIN       = 0b00000010;
    const SUPERADMIN  = 0b00000001;
}