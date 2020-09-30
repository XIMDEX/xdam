<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\Thumbnail\ThumbnailGeneratorInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class TestController extends Controller
{
    /**
     *  TODO : Delete this controller is only for testing purposes.
     *
     */
    public function index()
    {
        Artisan::call('getNewFilesFromCrawler');
    }

    public function watch()
    {
        Artisan::call('WatchDeletedFiles');
    }

    public function thumbnail(ThumbnailGeneratorInterface $thumbnailGenerator)
    {
        $file = new File();
        $file = $file->first();
        $file->delete();
        /*1
        $file = new File();
        $file = $file->first();
        $thumbnailGenerator->create($file);
        */
    }

}
