<?php

namespace App\Providers;

use App\Queue\Connectors\TikaQueueDatabaseConnector;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\QueueServiceProvider;

class TikaQueueServiceProvider extends QueueServiceProvider
{
    protected function registerDatabaseConnector($manager)
    {
        $manager->addConnector('database', function () {
            return new TikaQueueDatabaseConnector($this->app['db']);
        });
    }
}
