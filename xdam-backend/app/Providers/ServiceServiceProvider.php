<?php


namespace App\Providers;
use Illuminate\Support\ServiceProvider;


class ServiceServiceProvider extends ServiceProvider
{


    public function register()
    {
        $this->app->bind(
            'App\Services\Crawler\getFilesFromCrawlerServiceInterface',
            'App\Services\Crawler\getFilesFromCrawlerService',
            );
        $this->app->bind(
            'App\Services\Crawler\JobCrawlerServiceInterface',
            'App\Services\Crawler\JobCrawlerService',
        );
        $this->app->bind(
            'App\Services\File\FileServiceInterface',
            'App\Services\File\FileService',
        );
        $this->app->bind(
            'App\Services\File\FileIndexerServiceInterface',
            'App\Services\File\FileIndexerService',
        );
        $this->app->bind(
            'App\Services\Thumbnail\ThumbnailGeneratorInterface',
            'App\Services\Thumbnail\ThumbnailGeneratorService',
        );
        $this->app->bind(
            'App\Services\Dam\DamServiceInterface',
            'App\Services\Dam\DamService',
        );
    }
}
