<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v2\OrganizationController;

Route::group(['prefix' => 'organization', 'middleware' => 'manage.organizations_by_hash'], function () {

    Route::get('{organization_id}',               [OrganizationController::class, 'getOrganizationFromId'])->name('organization.get');
    
    // COLLECTIONS
    Route::get('{organization_id}/collections',   [OrganizationController::class, 'getOrganizationCollections'])->name('organization.collections.all');
});