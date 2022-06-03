<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v2\ResourceController;

Route::group(['prefix'=>'v2','as'=>'v2', 'middleware'=> 'auth:jwt'], function() {
    
    require_once base_path('routes/v2/organizations.php');
    require_once base_path('routes/v2/collections.php');
    require_once base_path('routes/v2/catalogue.php');
    require_once base_path('routes/v2/resource.php');

    Route::get('/resourcesSchema', [ResourceController::class, 'getSchemas'])->name('resources.schema');
    Route::get('/ini_pms', function () {
        return [
            'pms' => ini_get('post_max_size'),
            'mfu' => ini_get('max_file_uploads')
        ];
    })->name('ini.postMaxSize');
});