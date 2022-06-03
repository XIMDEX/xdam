<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResourceController;

Route::group(['prefix' => 'resource', 'middleware' => 'read.workspace'], function () {
    Route::group(['middleware' => 'create.resource'], function () {
        Route::post('/',                        [ResourceController::class, 'store'])->name('damResource.store');
        Route::post('/createBatch',             [ResourceController::class, 'storeBatch'])->name('damResource.store.batch');
        Route::post('/{collection_id}/create',  [ResourceController::class, 'store'])->name('collection.damResource.store');
        Route::post('/{damResource}/lomes',     [ResourceController::class, 'setLomesData'])->name('resources.setLomesData');
        Route::post('/{damResource}/lom',     [ResourceController::class, 'setLomData'])->name('resources.setLomData');
    });
});