<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\DamResource;

class BooksForConversionStored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Array of books added to convert
     *
     * @var DamResource[]
     */
    public $books;

    /**
     * Create a new event instance.
     * 
     * @param DamResource[] $books
     * @return void
     */
    public function __construct(array $books)
    {
        $this->books = $books;
    }
}
