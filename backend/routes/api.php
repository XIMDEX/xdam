<?php

use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\TagController;
use App\Models\DamResource;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['prefix'=>'v1','as'=>'v1'], function(){

    Route::group(['prefix' => 'category'], function() {
        Route::get('', [CategoryController::class, 'getAll'])->name('category.getAll');
        Route::get('/{category}', [CategoryController::class, 'get'])->name('category.get');
        Route::get('/{category}/getResources', [CategoryController::class, 'getResources'])->name('category.getResources');
        Route::post('/{category}', [CategoryController::class, 'update'])->name('category.update');
        Route::post('/', [CategoryController::class, 'store'])->name('category.store');
        Route::delete('/{category}', [CategoryController::class, 'delete'])->name('category.delete');
    });

    Route::get('/exploreCourses', [ResourceController::class, 'exploreCourses'])->name('damResource.exploreCourses');

    Route::group(['prefix' => 'resource'], function() {
        Route::get('/', [ResourceController::class, 'getAll'])->name('damResource.getAll');
        Route::get('/render/{damUrl}', [ResourceController::class, 'render'])->name('damResource.render');
        Route::get('/listTypes', [ResourceController::class, 'listTypes'])->name('damResource.listTypes');
        Route::get('/{damResource}', [ResourceController::class, 'get'])->name('damResource.get');
        Route::post('/{damResource}/update', [ResourceController::class, 'update'])->name('damResource.update');
        Route::post('/', [ResourceController::class, 'store'])->name('damResource.store');
        Route::post('/{damResource}/addPreview', [ResourceController::class, 'addPreview'])->name('damResource.addPreview');
        Route::post('/{damResource}/addFile', [ResourceController::class, 'addFile'])->name('damResource.addFile');
        Route::post('/{damResource}/addCategory/{category}', [ResourceController::class, 'addCategory'])->name('damResource.addCategory');
        Route::post('/{damResource}/addUse', [ResourceController::class, 'addUse'])->name('damResource.addUse');
        Route::delete('/{damResource}/deleteUse/{damResourceUse}', [ResourceController::class, 'deleteUse'])->name('damResource.deleteUse');
        Route::delete('/{damResource}', [ResourceController::class, 'delete'])->name('damResource.delete');
        Route::delete('/{damResource}/deleteCategory/{category}', [ResourceController::class, 'deleteCategory'])->name('damResource.deleteCategory');
    });

    Route::group(['prefix' => 'catalogue'], function() {
        Route::get('/{type}/index', [CatalogueController::class, 'index'])->name('catalogue.index');
        Route::get('/{type}', [CatalogueController::class, 'get'])->name('catalogue.get');
        Route::delete('/clearAll', [CatalogueController::class, 'delete'])->name('catalogue.delete');
    });

    Route::group(['prefix' => 'tag'], function() {
        Route::get('', [TagController::class, 'index'])->name('tag.index');
        Route::get('/{id}', [TagController::class, 'get'])->name('tag.show');
        Route::post('/{id}', [TagController::class, 'update'])->name('tag.update');
        Route::post('/', [TagController::class, 'store'])->name('tag.store');
        Route::delete('/{id}', [TagController::class, 'delete'])->name('tag.delete');
    });

});
