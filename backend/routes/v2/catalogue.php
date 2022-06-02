<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v2\CatalogueController;

Route::group(['prefix' => 'catalogue'], function () {
    Route::get('/{collection}', [CatalogueController::class, 'index'])->name('catalogue.index');
});
