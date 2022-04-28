<?php

declare(strict_types = 1);

namespace App\Services;

use App\Services\Solr\SolrService;
use App\Services\Solr\CoreHandlers\BookHandler;
use Solarium\Client;

class BookService
{
    const CLIENT_NAME = 'book';

    private Client $client;

    public function __construct(SolrService $solr)
    {
        $this->client = $solr->getClient(self::CLIENT_NAME);
    }

    private function execute($query)
    {
        $bookQuery = new BookHandler($query);

        $results = $this->client->execute($bookQuery->queryCoreSpecifics(null));

        $documentsResponse = [];

        foreach ($results as $result) {
            $fields = $result->getFields();
            $fields["data"] = @json_decode($fields["data"]);
            $documentsResponse[] = $fields;
        }

        return $documentsResponse;
    }

    public function bookLinks($isbn, $unit) {
        $query = $this->client
                    ->createSelect()
                    ->setQuery("data:*$isbn* data:*$unit*");

        $books = $this->execute($query);

        $results = array_filter($books, function($book) use($isbn, $unit) {
            if(!property_exists($book['data']->description, 'links')) {
                return false;
            }

            $bookIsbn = $book['data']->description->isbn ?? '';
            $bookUnit = $book['data']->description->unit ?? -1;

            return $book['data']->description->links !== null && 
                   $bookIsbn == $isbn &&
                   $bookUnit == $unit;
        });

        $links = array_map(function($book) {
            return $book['data']->description->links;
        }, array_values($results));

        return $links;
    }
}