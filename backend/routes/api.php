<?php

use App\Http\Controllers\AbilityController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\Book\BookUnitController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CDNController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CorporationController;
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

Route::group(['prefix' => 'v1', 'as' => 'v1'], function() {
    Route::get('temp', function() {
        return response()->json(['status' => 'OK']);
    });

    Route::group(['prefix' => 'cdn'], function() {
        // Route::get('/workspace_test/{workspace}',   [CatalogueController::class, 'getCatalogueByWorkspace'])
        //         ->name('cdn.getCatalogueByWorkspace');

        Route::group(['prefix' => 'admin'], function() {
            Route::post('/create',  [CDNController::class, 'createCDN'])
                    ->name('cdn.createCDN');
            Route::post('/remove',  [CDNController::class, 'removeCDN'])
                    ->name('cdn.removeCDN');

            Route::group(['prefix' => '{cdn_code}', 'middleware' => 'cdn.validCDN'], function () {
                Route::post('/generate_resource_hash',              [CDNController::class, 'createCDNResourceHash'])->name('cdn.createCDNResourceHash');
                Route::post('/generate_multiple_resources_hash',    [CDNController::class, 'createMultipleCDNResourcesHash'])->name('cdn.createMultipleCDNResourcesHash');
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
            Route::get('/{damResourceHash}',        [ResourceController::class, 'renderCDNResource'])->name('damResource.renderCDNResource');
            Route::get('/{damResourceHash}/{size}', [ResourceController::class, 'renderCDNResource'])->name('damResource.renderCDNResourceWithSize');
        });
    });

    Route::group(['prefix' => 'auth'], function() {
        Route::post('login',        [AuthController::class, 'login'])->name('auth.login');
        Route::post('signup',       [AuthController::class, 'signup'])->name('auth.signup');
        Route::post('kakumaLogin',  [RoleController::class, 'kakumaLogin'])->name('auth.kakumaLogin')->middleware('auth:api');
    });

    Route::group(['middleware' => 'show.resource'], function() {
        Route::group(['prefix' => 'resource'], function() {
            Route::get('/render/{damUrl}/{size}',   [ResourceController::class, 'render'])->name('damResource.renderWithSize');
            Route::get('/render/{damUrl}',          [ResourceController::class, 'render'])->name('damResource.render');
            Route::get('/{damResource}',            [ResourceController::class, 'get'])->name('damResource.get');
            Route::get('/lastCreated/{collection}', [CollectionController::class, 'getLastResourceCreated'])->name('collection.get.lastCreated');
            Route::get('/lastUpdated/{collection}', [CollectionController::class, 'getLastResourceUpdated'])->name('collection.get.lastUpdated');
        });
    });

    Route::get('/exploreCourses', [ResourceController::class, 'exploreCourses'])->name('damResource.exploreCourses');
    Route::get('/corporation/getDefault',                           [CorporationController::class, 'getDefault'])->name('corporation.getAll');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('/ini_pms', function() {
            return [
                'pms' => ini_get('post_max_size'),
                'mfu' => ini_get('max_file_uploads')
            ];
        })->name('ini.postMaxSize');

        Route::get('resourcesSchema',                   [ResourceController::class, 'resourcesSchema'])->name('resources.schemas');
        Route::get('lomesSchema',                       [ResourceController::class, 'lomesSchema'])->name('resources.lomes.schemas');
        Route::get('lomSchema',                         [ResourceController::class, 'lomSchema'])->name('resources.lom.schemas');
        Route::get('workspaceOfCollection/{collection}',[WorkspaceController::class, 'workspaceOfCollection'])->name('collection.org.wsp.get');

        Route::group(['prefix' => 'super-admin', 'middleware' => 'can:*'], function() {
            Route::group(['prefix' => 'organization'], function(){
                Route::post('create',               [OrganizationController::class, 'create'])->name('org.create');
                Route::get('get/{organization_id}', [OrganizationController::class, 'get'])->name('org.get');
                Route::get('index',                 [OrganizationController::class, 'index'])->name('org.index');
                Route::delete('/{organization_id}', [OrganizationController::class, 'delete'])->name('org.delete');
                Route::post('update',               [OrganizationController::class, 'update'])->name('org.update');
            });
        });

        Route::group(['prefix' => 'organization', 'middleware' => 'manage.organizations'], function(){
            //Roles
            Route::get('{organization}/role/{role_id}',      [RoleController::class, 'get'])->name('role.get');
            Route::get('{organization}/roles/all',           [RoleController::class, 'index'])->name('role.index');

            Route::post('{organization}/roles/store',        [RoleController::class, 'store'])->name('role.store');
            Route::post('{organization}/roles/update',       [RoleController::class, 'update'])->name('role.update');

            Route::post('{organization}/roles/set/ability',  [RoleController::class, 'setAbilityToRole'])->name('role.giveAbility');
            Route::post('{organization}/roles/unset/ability',[RoleController::class, 'setAbilityToRole'])->name('role.removeAbility');
            Route::delete('{organization}/roles/{id}',       [RoleController::class, 'delete'])->name('role.delete');

            //Abilities
            Route::get('{organization_id}/abilities/all',    [AbilityController::class, 'index']) ->name('ability.index');
            Route::get('{organization_id}/ability/{id}',     [AbilityController::class, 'get'])->name('ability.get');

            //User settings
            Route::post('set/user',                         [AdminController::class, 'setOrganizations'])->name('adm.usr.set.org');
            Route::post('unset/user',                       [AdminController::class, 'unsetOrganizations'])->name('adm.usr.unset.org');
            Route::post('workspace/create',                 [WorkspaceController::class, 'create'])->name('wsp.create');
            Route::post('workspace/setAll/user',            [AdminController::class, 'setAllWorkspacesOfOrganization'])->name('adm.usr.set.all.wsp');
            Route::get('{organization_id}/collection/all',  [OrganizationController::class, 'indexCollections'])->name('org.collection.list.all');

            Route::group(['prefix' => 'collection'], function(){
                Route::post('create',   [OrganizationController::class, 'createCollection'])->name('org.collection.create');
                Route::get('types/all', [OrganizationController::class, 'indexCollectionTypes'])->name('org.collectionType.all');
            });

            Route::get('{organization_id}/workspaces', [WorkspaceController::class, 'index'])->name('wsp.index');
        });

        Route::group(['prefix' => 'workspace'], function(){
            Route::get('/getMultiple',[WorkspaceController::class, 'getMultiple'])->name('wsp.getMutliple');

            Route::group(['middleware' => 'manage.workspaces'], function() {
                Route::post('set/user',             [AdminController::class, 'setWorkspaces'])  ->name('adm.usr.set.wsp');
                Route::post('unset/user',           [AdminController::class, 'unsetWorkspaces'])->name('adm.usr.unset.wsp');
                Route::get('/get/{workspace_id}',   [WorkspaceController::class, 'get'])->name('wsp.get');
                Route::post('update',               [WorkspaceController::class, 'update'])->name('wsp.update');
                Route::delete('/{workspace_id}',    [WorkspaceController::class, 'delete'])->name('wsp.delete');
            });
        });

        Route::group(['prefix' => 'role', 'middleware' => 'manage.roles'], function() {
            Route::post('user/set/abilitiesOnEntity',[AdminController::class, 'SetRoleAbilitiesOnEntity'])->name('adm.usr.set.role');
        });

        Route::group(['prefix' => 'user'], function(){
            Route::post('logout',   [AuthController::class, 'logout'])->name('user.logout');
            Route::get('/me',       [UserController::class, 'userInfo'])->name('user.get.me');
            Route::get('/',         [UserController::class, 'user'])->name('user.get');

            Route::group(['prefix' => 'resource'], function(){
                Route::get('/',                          [UserController::class, 'resources'])->name('user.get.resources');
                Route::get('/{damResource}/permissions', [UserController::class, 'resourceInfo'])->name('user.get.resource.info');
                /*
                    if the user is attached to the organization:
                    the next route attach the resource to the corporate workspace of an organization, and to the specified collection
                */
                Route::post('collection/attach',   [UserController::class, 'attachResourceToCollection'])->name('user.resource.collection.attach');
                Route::post('workspace/attach',    [UserController::class, 'attachResourceToWorkspace'])->name('user.resource.workspace.attach');
            });

            Route::group(['prefix' => 'workspaces'], function(){
                Route::get('/',                         [UserController::class, 'getWorkspaces'])->name('user.wsps.get');
                Route::post('/select',                  [UserController::class, 'selectWorkspace'])->name('user.select.workspace');
                Route::get('/{workspace_id}/resources', [WorkspaceController::class, 'getResources'])->name('user.wsp.get.resources');
            });

            Route::group(['prefix' => 'organizations'], function(){
                Route::get('/',                             [UserController::class, 'getOrganizations'])->name('user.org.get');
                Route::get('/{organization_id}/workspaces', [UserController::class, 'getWorkspacesOfOrganization'])->name('user.org.wsps.get');
                Route::get('/{organization_id}/resources',  [WorkspaceController::class, 'getOrganizationResources'])->name('user.org.get.resources');
            });
        });

        Route::group(['prefix' => 'resource', 'middleware' => 'read.workspace'], function() {
            Route::get('/listTypes', [ResourceController::class, 'listTypes'])->name('damResource.listTypes');
            Route::get('/',          [ResourceController::class, 'getAll'])->name('damResource.getAll');

            Route::get('/{damResource}/lomes',   [ResourceController::class, 'getLomesData'])->name('resources.getLomesData');
            Route::get('/{damResource}/lom',     [ResourceController::class, 'getLomData'])->name('resources.getLomData');
            Route::get('/{damResource}/restore', [ResourceController::class, 'restore'])->name('resources.restore');

            Route::group(['middleware' => 'create.resource'], function() {
                Route::post('/',                        [ResourceController::class, 'store'])->name('damResource.store');
                Route::post('/createBatch',             [ResourceController::class, 'storeBatch'])->name('damResource.store.batch');
                Route::post('/{collection_id}/create',  [ResourceController::class, 'store'])->name('collection.damResource.store');
                Route::post('/{damResource}/lomes',     [ResourceController::class, 'setLomesData'])->name('resources.setLomesData');
                Route::post('/{damResource}/lom',       [ResourceController::class, 'setLomData'])->name('resources.setLomData');

            });

            Route::group(['middleware' => 'download.resource'], function() {
                Route::get('/download/{damUrl}/{size}', [ResourceController::class, 'download'])->name('damResource.downloadWithSize');
                Route::get('/download/{damUrl}',        [ResourceController::class, 'download'])->name('damResource.download');
            });

            Route::group(['middleware' => 'update.resource'], function() {
                Route::get('/{damResource}/getMaxFiles',                    [ResourceController::class, 'getMaxFiles'])->name('damResource.getMaxFiles');
                Route::get('/{damResource}/getFilesCount',                  [ResourceController::class, 'getFilesCount'])->name('damResource.getFilesCount');
                Route::post('/{damResource}/update',                        [ResourceController::class, 'update'])->name('damResource.update');
                Route::post('/{damResource}/updateAsLastCreated',           [ResourceController::class, 'updateAsLastCreated'])->name('resource.updateAsLastCreated');
                Route::post('/{damResource}/updateAsLastUpdated',           [ResourceController::class, 'updateAsLastUpdated'])->name('resource.updateAsLastUpdated');
                Route::post('/{damResource}/updateAsOther/{otherResource}', [ResourceController::class, 'updateAsOther'])->name('resource.updateAsOther');
            });

            Route::group(['middleware' => 'update.resource.card'], function() {
                Route::post('/{damResource}/setTags',                   [ResourceController::class, 'setTags'])->name('damResource.setTags');
                Route::post('/{damResource}/addPreview',                [ResourceController::class, 'addPreview'])->name('damResource.addPreview');
                Route::post('/{damResource}/addFile',                   [ResourceController::class, 'addFile'])->name('damResource.addFile');
                Route::post('/{damResource}/addCategory/{category}',    [ResourceController::class, 'addCategory'])->name('damResource.addCategory');
                Route::post('/{damResource}/addUse',                    [ResourceController::class, 'addUse'])->name('damResource.addUse');
                Route::delete('/{damResource}/soft',                    [ResourceController::class, 'softDelete'])->name('damResource.softDelete');
                Route::delete('/{damResource}',                         [ResourceController::class, 'delete'])->name('damResource.delete');

            });

            Route::group(['middleware' => 'delete.resource.card'], function() {
                Route::delete('/{damResource}/deleteUse/{damResourceUse}',  [ResourceController::class, 'deleteUse'])->name('damResource.deleteUse');
                Route::delete('/{damResource}/deleteCategory/{category}',   [ResourceController::class, 'deleteCategory'])->name('damResource.deleteCategory');
                Route::delete('/{damResource}/associatedFile/{media}',      [ResourceController::class, 'deleteAssociatedFile'])->name('damResource.deleteAssociatedFile');
                Route::put('/{damResource}/deleteAssociatedFiles',          [ResourceController::class, 'deleteAssociatedFiles'])->name('damResource.deleteAssociatedFiles');
            });

            Route::group(['prefix' => 'book'], function() {
                Route::get('/{isbn}/{unit}/links',     [BookUnitController::class, 'retriveUnitLink'])->name('book.retriveUnitLink');
                Route::get('/{isbn}/links',            [BookUnitController::class, 'retriveAllUnitsLink'])->name('book.retriveUnitsLink');
                Route::post('/{isbn}/links',           [BookUnitController::class, 'updateLinks'])->name('book.updateLinks');
                Route::delete('/{isbn}/{unit}/links',  [BookUnitController::class, 'deleteUnitLink'])->name('book.deleteUnitLink');
                Route::delete('/{isbn}/links/',        [BookUnitController::class, 'deleteUnitsLink'])->name('book.deleteUnitsLink');
            });
        });

        Route::post('/resource/{damResource}/setWorkspace', [ResourceController::class, 'setWorkspace'])->name('damResource.setWorkspace')->middleware('read.workspace');

        Route::group(['prefix' => 'category'], function() {
            Route::get('',                                      [CategoryController::class, 'getAll'])->name('category.getAll');
            Route::get('/{category}',                           [CategoryController::class, 'get'])->name('category.get');
            Route::get('/{category}/getResources/{active?}',    [CategoryController::class, 'getResources'])->name('category.getResources');
            Route::post('/{category}',                          [CategoryController::class, 'update'])->name('category.update');
            Route::post('/',                                    [CategoryController::class, 'store'])->name('category.store');
            Route::delete('/{category}',                        [CategoryController::class, 'delete'])->name('category.delete');
        });


        Route::group(['prefix' => 'corporation'], function() {
            Route::get('',                                      [CorporationController::class, 'getAll'])->name('corporation.getAll');
            Route::get('/{corporation}',                        [CorporationController::class, 'get'])->name('corporation.get');
            Route::get('/{corporation}/getResources/{active?}', [CorporationController::class, 'getResources'])->name('corporation.getResources');
            Route::post('/{corporation}',                       [CorporationController::class, 'update'])->name('corporation.update');
            Route::post('/',                                    [CorporationController::class, 'store'])->name('corporation.store');
            Route::delete('/{corporation}',                     [CorporationController::class, 'delete'])->name('corporation.delete');
        });

        Route::group(['prefix' => 'catalogue'], function() {
            Route::get('/{collection}',                         [CatalogueController::class, 'index'])->name('catalogue.index');
        });

        Route::group(['prefix' => 'tag'], function() {
            Route::get('',          [TagController::class, 'index'])->name('tag.index');
            Route::get('/{id}',     [TagController::class, 'get'])->name('tag.show');
            Route::post('/{id}',    [TagController::class, 'update'])->name('tag.update');
            Route::post('/',        [TagController::class, 'store'])->name('tag.store');
            Route::delete('/{id}',  [TagController::class, 'delete'])->name('tag.delete');
        });
    });
});
