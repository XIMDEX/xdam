<?php

use App\Http\Controllers\CollectionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'collection'], function () {

    Route::get('{collection_id}',               [CollectionController::class, 'get'])->name('collection.get');
});
