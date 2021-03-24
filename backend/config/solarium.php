<?php

use App\Enums\ResourceType;

return [
    'connections' => [
        'activity' => [
            'endpoint' => [
                'scheme' => 'http', # or https
                'host' => env('SOLR_HOST', 'localhost'),
                'port' => env('SOLR_PORT', '8983'),
                'path' => env('SOLR_PATH', '/'),
                'core' => 'activity',
            ],
            'resource' => 'ActivitySolrResource',
            'accepts_types' => [ResourceType::activity]
        ],
        'assessment' => [
            'endpoint' => [
                'scheme' => 'http', # or https
                'host' => env('SOLR_HOST', 'localhost'),
                'port' => env('SOLR_PORT', '8983'),
                'path' => env('SOLR_PATH', '/'),
                'core' => 'assessment',
            ],
            'resource' => 'AssessmentSolrResource',
            'accepts_types' => [ResourceType::assessment]
        ],

        'course' => [
            'endpoint' => [
                'scheme' => 'http', # or https
                'host' => env('SOLR_HOST', 'localhost'),
                'port' => env('SOLR_PORT', '8983'),
                'path' => env('SOLR_PATH', '/'),
                'core' => 'course',
            ],
            'resource' => 'CourseSolrResource',
            'accepts_types' => [ResourceType::course]
        ],

        'multimedia' => [
            'endpoint' => [
                'scheme' => 'http', # or https
                'host' => env('SOLR_HOST', 'localhost'),
                'port' => env('SOLR_PORT', '8983'),
                'path' => env('SOLR_PATH', '/'),
                'core' => 'multimedia',
                'timeout' => 120,
            ],
            'resource' => 'MultimediaSolrResource',
            'accepts_types' => [ResourceType::multimedia]
        ],
        'book' => [
            'endpoint' => [
                'scheme' => 'http', # or https
                'host' => env('SOLR_HOST', 'localhost'),
                'port' => env('SOLR_PORT', '8983'),
                'path' => env('SOLR_PATH', '/'),
                'core' => 'book',
            ],
            'resource' => 'BookSolrResource',
            'accepts_types' => [ResourceType::book]
        ]
    ],
    'solr_validators_folder' => env('SOLR_VALIDATORS_FOLDER', ''),
    'solr_schemas_folder' => env('SOLR_SCHEMAS_FOLDER', ''),

];
