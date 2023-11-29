<?php

use App\Http\Controllers\Solr\SolrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response()->json(['status' => 'OK', 'message' => 'contact with your provider']);
});


Route::group(['prefix' => 'solr'], function() {
    Route::any('/{core}/{action}', [SolrController::class, 'handle'])->name('solr.handleQuerySolr');
});
