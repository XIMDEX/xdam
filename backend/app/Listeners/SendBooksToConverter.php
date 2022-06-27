<?php

namespace App\Listeners;

use App\Events\BooksForConversionStored;
use Illuminate\Support\Facades\Http;

class SendBooksToConverter
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  BoksForConversionStored  $event
     * @return void
     */
    public function handle(BooksForConversionStored $event)
    {
        $ids = array_map(fn($book) => $book->id, $event->books);

        $scormUrl = getenv("SCORM_URL");

        if(is_null($scormUrl)) {
            return; 
        }

        $url = $scormUrl. "/convert";

        Http::post($url, [
            'books' => $ids,
        ]);
    }
}
