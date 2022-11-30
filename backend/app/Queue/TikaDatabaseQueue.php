<?php

namespace App\Queue;

use Illuminate\Queue\DatabaseQueue;
use Throwable;
use Illuminate\Support\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\DatabaseJobRecord;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Auth;
use PDOException;

class TikaDatabaseQueue extends DatabaseQueue
{
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0)
    {
        $payloadDecoded = json_decode($payload);
        $rawBody = unserialize($payloadDecoded->data->command);

        return [
            'queue' => $queue,
            'attempts' => $attempts,
            'reserved_at' => null,
            'available_at' => $availableAt,
            'created_at' => $this->currentTime(),
            'payload' => $payload,
            'resource_id' => $rawBody->resource_id,
            'media_id' => $rawBody->media_id
        ];
    }

    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        $entry = null;

        try {
            $entry = $this->database->table($this->table)->insertGetId($this->buildDatabaseRecord(
                $this->getQueue($queue), $payload, $this->availableAt($delay), $attempts
            ));            
        } catch (PDOException $e) {
            // echo $e->getMessage();
            throw $e;
        }

        return $entry;
    }
}