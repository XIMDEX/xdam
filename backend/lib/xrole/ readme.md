```
X   X  RRRR   OOO  L     EEEE
 X X   R   R O   O L     E
  X    RRRR  O   O L     EEE
 X X   R  R  O   O L     E
X   X  R   R  OOO  LLLLL EEEE
```

# Ximdex Xrole

Ximdex Xrole is a PHP library for bitwise permissions management. It allows for a flexible and granular permission system using bitwise operations.

## Installation

To install Ximdex Xrole, use Composer:

```bash
composer require ximdex/xrole
```

## Requirements
Ximdex Xrole requires PHP 8.2 or higher.
Usage
To use the library, you need to instantiate the Permissions class and use its methods to check permissions.
```
use Ximdex\Xrole\Models\Permissions;
use Ximdex\Xrole\Contracts\PermissionConstants;

// Instantiate Permissions with an initial permission
$permissions = new Permissions(PermissionConstants::READ);

// Check if a permission is set
if ($permissions->hasPermission(PermissionConstants::READ)) {
    // The READ permission is set
}

// Combine permissions with bitwise OR to set multiple permissions
$permissions = new Permissions(PermissionConstants::READ | PermissionConstants::SEARCH);
$permissions = new Permissions($combinedPermissions);

// Check for combined permissions
if ($permissions->hasPermission(PermissionConstants::SEARCH)) {
    // The SEARCH permission is set
}
```