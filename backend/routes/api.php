<?php

use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\TagController;
use App\Models\DamResource;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkspaceController;

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

    Route::group(['prefix' => 'organization'], function(){
        Route::post('create',   [OrganizationController::class, 'create'])->name('org.create');
        Route::get('get/{id}',  [OrganizationController::class, 'get'])->name('org.get');
        Route::get('index',     [OrganizationController::class, 'index'])->name('org.index');
        Route::delete('/{id}',  [OrganizationController::class, 'delete'])->name('org.delete');
        Route::post('update',   [OrganizationController::class, 'update'])->name('org.update');
    });

    Route::group(['prefix' => 'workspace'], function(){
        Route::post('create',   [WorkspaceController::class, 'create'])->name('wsp.create');
        Route::get('get/{id}',  [WorkspaceController::class, 'get'])->name('wsp.get');
        Route::get('index',     [WorkspaceController::class, 'index'])->name('wsp.index');
        Route::delete('/{id}',  [WorkspaceController::class, 'delete'])->name('wsp.delete');
        Route::post('update',   [WorkspaceController::class, 'update'])->name('wsp.update');
    });

    Route::group(['prefix' => 'role'], function(){
        Route::post('/store',           [RoleController::class, 'store'])->name('role.store');
        Route::post('/update',          [RoleController::class, 'update'])->name('role.update');
        Route::post('/getByName',       [RoleController::class, 'getByName'])->name('role.getByName');
        Route::get('/all',              [RoleController::class, 'index'])->name('role.index');
        Route::get('/{id}',             [RoleController::class, 'getById'])->name('role.getById');
        Route::delete('/{id}',          [RoleController::class, 'delete'])->name('role.delete');
        Route::post('/givePermission',  [RoleController::class, 'givePermission'])->name('role.givePermission');
        Route::post('/revokePermission',[RoleController::class, 'revokePermission'])->name('role.revokePermission');
    });

    Route::group(['prefix' => 'permission'], function(){
        Route::post('/store',       [PermissionController::class, 'store'])->name('permission.store');
        Route::post('/update',      [PermissionController::class, 'update'])->name('permission.update');
        Route::post('/getByName',   [PermissionController::class, 'getByName'])->name('permission.getByName');
        Route::get('/all',          [PermissionController::class, 'index'])->name('permission.index');
        Route::get('/{id}',         [PermissionController::class, 'getById'])->name('permission.getById');
        Route::delete('/{id}',      [PermissionController::class, 'delete'])->name('permission.delete');
    });

    Route::group(['prefix' => 'auth'], function(){
        Route::post('login',    [AuthController::class, 'login']    )->name('auth.login');
        Route::post('signup',   [AuthController::class, 'signup']   )->name('auth.signup');
    });

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('user',      [UserController::class, 'user_auth'])->name('user.get');
        Route::post('logout',   [AuthController::class, 'logout'])->name('user.logout');
    });

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
        Route::get('/{damResource}/download', [ResourceController::class, 'download'])->name('damResource.download');
        Route::get('/listTypes', [ResourceController::class, 'listTypes'])->name('damResource.listTypes');
        Route::get('/{damResource}', [ResourceController::class, 'get'])->name('damResource.get');
        Route::post('/{damResource}/update', [ResourceController::class, 'update'])->name('damResource.update');
        Route::post('/', [ResourceController::class, 'store'])->name('damResource.store');
        Route::post('/{damResource}/addPreview', [ResourceController::class, 'addPreview'])->name('damResource.addPreview');
        Route::post('/{damResource}/setTags', [ResourceController::class, 'setTags'])->name('damResource.setTags');
        Route::post('/{damResource}/addFile', [ResourceController::class, 'addFile'])->name('damResource.addFile');
        Route::post('/{damResource}/addCategory/{category}', [ResourceController::class, 'addCategory'])->name('damResource.addCategory');
        Route::post('/{damResource}/addUse', [ResourceController::class, 'addUse'])->name('damResource.addUse');
        Route::delete('/{damResource}/deleteUse/{damResourceUse}', [ResourceController::class, 'deleteUse'])->name('damResource.deleteUse');
        Route::delete('/{damResource}', [ResourceController::class, 'delete'])->name('damResource.delete');
        Route::delete('/{damResource}/deleteCategory/{category}', [ResourceController::class, 'deleteCategory'])->name('damResource.deleteCategory');
    });

    Route::group(['prefix' => 'catalogue'], function() {
        Route::get('/checkSolr', [CatalogueController::class, 'checkSolr'])->name('catalogue.checkSolr');
        Route::get('/{collection}/index', [CatalogueController::class, 'index'])->name('catalogue.index');
        Route::get('/{collection}', [CatalogueController::class, 'get'])->name('catalogue.get');
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
