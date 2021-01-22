<?php


namespace App\Services\Solr;


class CourseSchema
{
    public string $active =
        '{
            "name": "active",
            "type": "boolean",
            "omitNorms": true,
            "omitTermFreqAndPositions": true,
            "indexed": true,
            "stored": true,
            "uninvertible": true,
            "sortMissingLast": true,
            "omitPositions": true
        }';

    public string $categories =
        '{
            "name": "categories",
            "type": "text_general"
        }';

    public string $category =
        '{
            "name": "category",
            "type": "string",
            "omitNorms": true,
            "omitTermFreqAndPositions": true,
            "indexed": true,
            "stored": true,
            "uninvertible": true,
            "sortMissingLast": true,
            "docValues": true,
            "multiValued": true
        }';
    public string $data =
        '{
            "name": "data",
            "type": "string",
            "omitNorms": true,
            "omitTermFreqAndPositions": true,
            "indexed": true,
            "stored": true,
            "uninvertible": true,
            "sortMissingLast": true,
            "docValues": false,
            "omitPositions": true
        }';
    public string $files =
        '{
            "name": "files",
            "type": "text_general"
        }';
    public string $id =
        '{
            "name": "id",
            "type": "string",
            "multiValued": false,
            "indexed": true,
            "required": true,
            "stored": true
        }';

    public string $name =
        '{
            "name": "name",
            "type": "string",
            "omitNorms": true,
            "omitTermFreqAndPositions": true,
            "indexed": true,
            "stored": true,
            "uninvertible": true,
            "sortMissingLast": true,
            "docValues": true,
            "omitPositions": true
        }';
    public string $previews =
        '{
            "name": "previews",
            "type": "strings",
            "omitNorms": true,
            "omitTermFreqAndPositions": true,
            "indexed": true,
            "stored": true,
            "uninvertible": true,
            "sortMissingLast": true,
            "docValues": false,
            "omitPositions": true,
            "multiValued": true
        }';
    public string $type =
        '{
            "name": "type",
            "type": "string",
            "omitNorms": true,
            "omitTermFreqAndPositions": true,
            "indexed": true,
            "stored": true,
            "uninvertible": true,
            "sortMissingLast": true,
            "docValues": true,
            "omitPositions": true
        }';

}
