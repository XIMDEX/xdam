<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DamResource;
use App\Services\Solr\SolrService;
use App\Services\Solr\CoreHandlers\BookHandler;
use Solarium\Client;
use \Error;

class BookService
{
    const CLIENT_NAME = 'book';

    private Client $client;

    public function __construct(SolrService $solr)
    {
        $this->client = $solr->getClient(self::CLIENT_NAME);
        $this->solrSerice = $solr;
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

    public function bookLink(string $isbn, int $unit): ?string
    {
        $query = $this->client
            ->createSelect()
            ->setQuery("data:*$isbn*");

        $results = $this->execute($query);

        $books = array_filter($results, function ($book) use ($isbn) {
            if (!property_exists($book['data']->description, 'isbn')) {
                return false;
            }

            return $book['data']->description->isbn == $isbn;
        });

        $book = $books[0];

        if (!property_exists($book['data']->description, 'links')) {
            return [];
        }

        $links = (array) $book['data']->description->links;

        return $links[$unit];
    }

    public function findBookIdFromIsbn(string $isbn)
    {
        $query = $this->client
            ->createSelect()
            ->setQuery("data:*$isbn*");

        $results = $this->execute($query);

        if (count($results) > 1) {
            throw new Error("Several books found with isbn ${isbn}");
        }

        return $results[0]["id"];
    }

    public function findBookFromIsbn(string $isbn)
    {
        $query = $this->client
            ->createSelect()
            ->setQuery("data:*$isbn*");

        $results = $this->execute($query);

        if (count($results) > 1) {
            throw new Error("Several books found with isbn ${isbn}");
        }

        return DamResource::find($results[0]["id"]);
    }

    /**
     * @param DamResource $book
     * @param string[] $units
     */
    public function updateBookLinks(DamResource $book, array $links)
    {
        $hightsUnit = max(array_keys($links));
        $lowestUnit = min(array_keys($links));

        if ($hightsUnit > $book->data->description->unit || $lowestUnit < 0) {
            throw new Error('Invalid unit number');
        }

        $dataForUpdate = $book->data;
        $dataForUpdate->description->links = (object) $links;

        $book->update([
            'data' => $dataForUpdate
        ]);

        $book = $book->fresh();
        $this->solrSerice->saveOrUpdateDocument($book);
    }

    /**
     * @param DamResource $book
     * @param int[] $units
     */
    public function deleteBookUnitsLink(DamResource $book, array $units): void
    {
        $dataForUpdate = $book->data;

        foreach ($units as $unit) {
            unset($dataForUpdate->description->links->{$unit});
        }

        $book->update([
            'data' => $dataForUpdate
        ]);

        $book = $book->fresh();
        $this->solrSerice->saveOrUpdateDocument($book);
    }

    public function deleteBookAllUnitsLink(DamResource $book): void
    {
        $maxUnit = $book->data->description->unit;

        $this->deleteBookUnitsLink($book, range(0, $maxUnit));
    }
}
