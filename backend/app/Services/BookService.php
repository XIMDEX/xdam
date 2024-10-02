<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DamResource;
use App\Services\Solr\SolrService;
use App\Services\Solr\CoreHandlers\BookHandler;
use Solarium\Client;
use \Error;
use Intervention\Image\Exception\NotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use App\Enums\ResourceType;


class BookService extends BaseService
{
    const CLIENT_NAME = 'book';

    private Client $client;
    private $solrSerice;

    public function __construct(SolrService $solr)
    {
        parent::__construct();
        self::$type_service = ResourceType::book;
        self::$array = ['workspaces' => 'Workspace'];
        $this->client = $solr->getClient(self::CLIENT_NAME);
        $this->solrSerice = $solr;
    }

    public static function handleFacetCard($facets)
    {
        $facets = parent::handleFacetCard($facets);
        $facets = self::addWorkspace(ResourceType::book,$facets,array_keys(self::$array));

        return $facets;
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

    public function bookUnitLink(DamResource $book, int $unit): ?string
    {
        $description = $book['data']->description;

        if (!property_exists($description, 'links')) {
            return null;
        }

        if (!property_exists($description->links, (string) $unit)) {
            throw new NotFoundException("Unit $unit link not found");
        }

        return $description->links->{$unit};
    }

    /**
     * @param DamResource $book
     * @return object|null
     */
    public function allBookUnitsLink(DamResource $book): ?object
    {
        $description = $book['data']->description;

        if (!property_exists($description, 'links')) {
            return null;
        }

        return $description->links;
    }

    public function findBookFromIsbn(string $isbn)
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

        if (count($books) > 1) {
            throw new Error("Several books found with isbn $isbn");
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

    public function deleteBookUnitLink(DamResource $book, int $unit): void
    {
        $dataForUpdate = $book->data;

        if(!property_exists($dataForUpdate->description->links, "$unit")) {
            throw new ResourceNotFoundException("Unit $unit does not have any link");
        }

        unset($dataForUpdate->description->links->{$unit});

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

        foreach($units as $unit) {
            unset($dataForUpdate->description->links->{$unit});
        }

        $book->update([
            'data' => $dataForUpdate
        ]);

        $book = $book->fresh();
        $this->solrSerice->saveOrUpdateDocument($book);
    }
}
