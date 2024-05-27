<?php

namespace Tests\Unit;

use App\Jobs\Xowl\ProcessXowlDocument;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class QueueTest extends TestCase
{
    public function testQueueJob()
    {
        Queue::fake();

          // Define a fake job using an anonymous class
          $fakeJob = new class {
            public function handle()
            {
                return true;
            }
        };

        // Dispatch the fake job
        dispatch($fakeJob);

        // Assert that the fake job was pushed to the queue
        Queue::assertPushed(get_class($fakeJob), function ($job) {
            return true; // Add any specific assertions here if needed
        });
    }
}
