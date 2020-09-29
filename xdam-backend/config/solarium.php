<?php

return [

    'default' => env('SOLR_CONNECTION', 'main'),

    'connections' => [
        'main' => [
            'timeout' => env('SOLR_TIMEOUT', 60),
            'host' => env('SOLR_HOST', 'localhost'),
            'port' => env('SOLR_PORT', '8983'),
            'path' => env('SOLR_PATH', '/'),

            // Set default core (or collection in case of solr cloud)
            // Can be changed dynamically via `$solr->getEndpoint()->setCollection('the-collection')`
            // 'core' => 'the-core'
            // 'collection' => 'the-collection'
        ],
    ],
];
