<?php

use App\Http\Controllers\AbilityController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OrganizationController;
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

    Route::group(['prefix' => 'auth'], function(){
        Route::post('login',    [AuthController::class, 'login'])->name('auth.login');
        Route::post('signup',   [AuthController::class, 'signup'])->name('auth.signup');
    });

    Route::group(['middleware' => 'auth:api'], function () {
        Route::group(['prefix' => 'admin', 'middleware' => 'can:*'], function(){
            Route::post('user/setOrganizations',                [AdminController::class, 'setOrganizations'])->name('adm.usr.set.org');
            Route::post('user/setWorkspaces',                   [AdminController::class, 'setWorkspaces'])->name('adm.usr.set.wsp');
            Route::post('user/unsetOrganizations',              [AdminController::class, 'unsetOrganizations'])->name('adm.usr.unset.org');
            Route::post('user/unsetWorkspaces',                 [AdminController::class, 'unsetWorkspaces'])->name('adm.usr.unset.wsp');
            Route::post('user/set/roleAbilitiesOnWorkspace',    [AdminController::class, 'setRoleAbilitiesOnWorkspace'])->name('adm.usr.set.role');
            Route::post('user/unset/roleAbilitiesOnWorkspace',  [AdminController::class, 'unsetRoleAbilitiesOnWorkspace'])->name('adm.usr.unset.role');

            Route::group(['prefix' => 'role'], function() {
                Route::post('store',            [RoleController::class, 'store'])->name('role.store');
                Route::post('update',           [RoleController::class, 'update'])->name('role.update');
                Route::get('all',               [RoleController::class, 'index'])->name('role.index');
                Route::get('/{id}',             [RoleController::class, 'get'])->name('role.get');
                Route::delete('/{id}',          [RoleController::class, 'delete'])->name('role.delete');
                Route::post('giveAbility',      [RoleController::class, 'giveAbility'])->name('role.giveAbility');
                Route::post('removeAbility',    [RoleController::class, 'removeAbility'])->name('role.removeAbility');
            });
            Route::group(['prefix' => 'ability'], function(){
                Route::post('/store',   [AbilityController::class, 'store'])->name('ability.store');
                Route::post('/update',  [AbilityController::class, 'update'])->name('ability.update');
                Route::get('/all',      [AbilityController::class, 'index'])->name('ability.index');
                Route::get('/{id}',     [AbilityController::class, 'get'])->name('ability.get');
                Route::delete('/{id}',  [AbilityController::class, 'delete'])->name('ability.delete');
            });

        });

        Route::group(['prefix' => 'organization'], function(){
            Route::post('create',               [OrganizationController::class, 'create'])  ->name('org.create');
            Route::get('get/{organization_id}', [OrganizationController::class, 'get'])     ->name('org.get')   ;
            Route::get('index',                 [OrganizationController::class, 'index'])   ->name('org.index') ;
            Route::delete('/{organization_id}', [OrganizationController::class, 'delete'])  ->name('org.delete');
            Route::post('update',               [OrganizationController::class, 'update'])  ->name('org.update');
            Route::group(['prefix' => 'workspace'], function(){
                Route::post('create',               [WorkspaceController::class, 'create']) ->name('wsp.create');
                Route::get('/get/{workspace_id}',   [WorkspaceController::class, 'get'])    ->name('wsp.get');
                Route::get('index',                 [WorkspaceController::class, 'index'])  ->name('wsp.index');
                Route::delete('/{workspace_id}',    [WorkspaceController::class, 'delete']) ->name('wsp.delete');
                Route::post('update',               [WorkspaceController::class, 'update']) ->name('wsp.update');
            });
        });

        Route::group(['prefix' => 'user'], function(){
            Route::post('logout',                                   [AuthController::class, 'logout'])->name('user.logout');
            Route::get('/',                                         [UserController::class, 'user'])->name('user.model.get');
            Route::get('workspaces',                                [UserController::class, 'getWorkspaces'])->name('user.wsps.get');
            Route::get('organizations',                             [UserController::class, 'getOrganizations'])->name('user.org.get');
            Route::get('organization/{organization_id}/workspaces', [UserController::class, 'getWorkspacesOfOrganization'])->name('user.org.wsps.get');
        });
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
        Route::get('/download/{damResource}', [ResourceController::class, 'download'])->name('damResource.download');
        Route::get('/listTypes', [ResourceController::class, 'listTypes'])->name('damResource.listTypes');
        Route::get('/{damResource}', [ResourceController::class, 'get'])->name('damResource.get');
        Route::post('/{damResource}/update', [ResourceController::class, 'update'])->name('damResource.update');
        Route::post('/', [ResourceController::class, 'store'])->name('damResource.store');
        Route::post('/{damResource}/setTags', [ResourceController::class, 'setTags'])->name('damResource.setTags');
        Route::post('/{damResource}/addPreview', [ResourceController::class, 'addPreview'])->name('damResource.addPreview');
        Route::post('/{damResource}/addFile', [ResourceController::class, 'addFile'])->name('damResource.addFile');
        Route::post('/{damResource}/addCategory/{category}', [ResourceController::class, 'addCategory'])->name('damResource.addCategory');
        Route::post('/{damResource}/addUse', [ResourceController::class, 'addUse'])->name('damResource.addUse');
        Route::delete('/{damResource}/deleteUse/{damResourceUse}', [ResourceController::class, 'deleteUse'])->name('damResource.deleteUse');
        Route::delete('/{damResource}', [ResourceController::class, 'delete'])->name('damResource.delete');
        Route::delete('/{damResource}/deleteCategory/{category}', [ResourceController::class, 'deleteCategory'])->name('damResource.deleteCategory');
        Route::delete('/{damResource}/associatedFile/{media}', [ResourceController::class, 'deleteAssociatedFile'])->name('damResource.deleteAssociatedFile');
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
