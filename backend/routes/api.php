<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\TestingController;
use Illuminate\Http\Request;
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

Route::group(['middleware' => ['cors']], function () {
    //Rutas a las que se permitirá acceso
});

Route::group(['prefix' => 'v1'], function() {

    Route::get('/token/revoke', function (Request $request) {
        $userId = $request->get('user');
        if (null != $userId) {
            DB::table('oauth_access_tokens')
                ->where('user_id', $userId)
                    ->update([
                        'revoked' => true
                    ]);
        }
        return response()->json('DONE');
    });

    Route::group(['prefix' => 'resources'], function() {
        Route::get('', [ResourceController::class, 'index'])->name('resource.index');
        Route::get('/dam', [ResourceController::class, 'index'])->name('resource.dam.index');
        Route::get('/{id}', [ResourceController::class, 'show'])->name('resource.show');
        Route::get('/{id}/image', [ResourceController::class, 'image'])->name('resource.image');
        Route::get('/{id}/file', [ResourceController::class, 'downloadFile'])->name('resource.file');
        Route::put('/{id}', [ResourceController::class, 'update'])->name('resource.update');
        Route::post('/', [ResourceController::class, 'store'])->name('resource.store');
        Route::delete('/{id}', [ResourceController::class, 'delete'])->name('resource.delete');
    });

});
