<?php

namespace App\Queue\Connectors;

use App\Queue\TikaDatabaseQueue;
use Illuminate\Queue\Connectors\DatabaseConnector;

class TikaQueueDatabaseConnector extends DatabaseConnector
{
    public function connect(array $config)
    {
        return new TikaDatabaseQueue(
            $this->connections->connection($config['connection'] ?? null),
            $config['table'],
            $config['queue'],
            $config['retry_after'] ?? 60
        );
    }
}