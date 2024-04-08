<?php

use App\Http\Controllers\CDNController;
use App\Http\Controllers\CDNHashController;
use App\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'admin'], function() {
    Route::post('/create',  [CDNController::class, 'createCDN'])
            ->name('cdn.createCDN');
    Route::post('/remove',  [CDNController::class, 'removeCDN'])
            ->name('cdn.removeCDN');

    Route::group(['prefix' => '{cdn_code}', 'middleware' => 'cdn.validCDN'], function () {
        Route::post('/generate_resource_hash',              [CDNHashController::class, 'createCDNResourceHash'])->name('cdn.createCDNResourceHash');
        Route::post('/generate_multiple_resources_hash',    [CDNHashController::class, 'createMultipleCDNResourcesHash'])->name('cdn.createMultipleCDNResourcesHash');
        Route::post('/generate_collection_resources_hash',  [CDNController::class, 'createCDNCollectionResourcesHash'])->name('cdn.createCDNCollectionResourcesHash');
    });

    Route::group(['prefix' => 'collection'], function() {
        Route::post('/add',     [CDNController::class, 'addCollection'])->name('cdn.addCDNCollection');
        Route::post('/remove',  [CDNController::class, 'removeCollection'])->name('cdn.removeCDNCollection');
        Route::post('/check',   [CDNController::class, 'checkCollection'])->name('cdn.checkCDNCollection');
        Route::post('/list',    [CDNController::class, 'listCollections'])->name('cdn.listCDNCollections');
    });

    Route::group(['prefix' => 'access_permission'], function() {
        Route::post('/update',  [CDNController::class, 'updateAccessPermission'])->name('cdn.updateCDNAccessPermission');

        Route::group(['prefix' => 'rule'], function() {
            Route::post('/add',     [CDNController::class, 'addAccessPermissionRule'])->name('cdn.addCDNAccessPermissionRule');
            Route::post('/remove',  [CDNController::class, 'removeAccessPermissionRule'])->name('cdn.removeCDNAccessPermissionRule');
            Route::post('/list',    [CDNController::class, 'listAccessPermissionRules'])->name('cdn.listCDNAccessPermissionRules');
        });
    });
   
});
Route::group(['prefix' => 'resource'], function() {
    Route::get('/{damResourceHash}/render',        [ResourceController::class, 'renderCDNResourceFile'])->name('damResource.renderCDNResource');
    Route::get('/{damResourceHash}',        [ResourceController::class, 'renderCDNResource'])->name('damResource.previewCDNResource');
    Route::get('/{damResourceHash}/{size}', [ResourceController::class, 'renderCDNResource'])->name('damResource.renderCDNResourceWithSize');
});
Route::get('cdns', [CDNController::class, 'getCDNs'])->name('cdns.index');