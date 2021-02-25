<?php

return [
    'connections' => [
        'main' => [
            'endpoint' => [
                'scheme' => 'http', # or https
                'host' => env('SOLR_HOST', 'localhost'),
                'port' => env('SOLR_PORT', '8983'),
                'path' => env('SOLR_PATH', '/'),
                'core' => 'main',
            ],
            'resource' => 'CourseSolrResource'
        ],

        'course' => [
            'endpoint' => [
                'scheme' => 'http', # or https
                'host' => env('SOLR_HOST', 'localhost'),
                'port' => env('SOLR_PORT', '8983'),
                'path' => env('SOLR_PATH', '/'),
                'core' => 'course',
            ],
            'resource' => 'CourseSolrResource'
        ],

        'multimedia' => [
            'endpoint' => [
                'scheme' => 'http', # or https
                'host' => env('SOLR_HOST', 'localhost'),
                'port' => env('SOLR_PORT', '8983'),
                'path' => env('SOLR_PATH', '/'),
                'core' => 'multimedia',
            ],
            'resource' => 'CourseSolrResource'
        ]
    ],
    'solr_validators_folder' => env('SOLR_VALIDATORS_FOLDER', ''),
    'solr_schemas_folder' => env('SOLR_SCHEMAS_FOLDER', ''),

];
