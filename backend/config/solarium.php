<?php

use App\Enums\ResourceType;

return [
    'connections'   => [
        'activity'  => [
            'endpoint'      => [
                'scheme'    => 'http', # or https
                'host'      => env('SOLR_HOST', 'localhost'),
                'port'      => env('SOLR_PORT', '8983'),
                'path'      => env('SOLR_PATH', '/'),
                'core'      => 'activity',
            ],
            'resource'      => 'ActivitySolrResource',
            'classHandler'  => 'ActivityHandler',
            'accepts_types' => [ResourceType::activity]
        ],
        'assessment'    => [
            'endpoint'      => [
                'scheme'    => 'http', # or https
                'host'      => env('SOLR_HOST', 'localhost'),
                'port'      => env('SOLR_PORT', '8983'),
                'path'      => env('SOLR_PATH', '/'),
                'core'      => 'assessment',
            ],
            'resource'      => 'AssessmentSolrResource',
            'classHandler'  => 'AssessmentHandler',
            'accepts_types' => [ResourceType::assessment]
        ],
        'course'    => [
            'endpoint'      => [
                'scheme'    => 'http', # or https
                'host'      => env('SOLR_HOST', 'localhost'),
                'port'      => env('SOLR_PORT', '8983'),
                'path'      => env('SOLR_PATH', '/'),
                'core'      => 'course',
            ],
            'resource'      => 'CourseSolrResource',
            'classHandler'  => 'CourseHandler',
            'accepts_types' => [ResourceType::course]
        ],
        'multimedia'    => [
            'endpoint'      => [
                'scheme'    => 'http', # or https
                'host'      => env('SOLR_HOST', 'localhost'),
                'port'      => env('SOLR_PORT', '8983'),
                'path'      => env('SOLR_PATH', '/'),
                'core'      => 'multimedia',
                'timeout'   => 120,
            ],
            'resource'      => 'MultimediaSolrResource',
            'classHandler'  => 'MultimediaHandler',
            'accepts_types' => [ResourceType::multimedia]
        ],
        'book'  => [
            'endpoint'      => [
                'scheme'    => 'http', # or https
                'host'      => env('SOLR_HOST', 'localhost'),
                'port'      => env('SOLR_PORT', '8983'),
                'path'      => env('SOLR_PATH', '/'),
                'core'      => 'book',
            ],
            'resource'      => 'BookSolrResource',
            'classHandler'  => 'BookHandler',
            'accepts_types' => [ResourceType::book]
        ],
        
        'document' => [
            'endpoint' => [
                'scheme' => 'http', # or https
                'host' => env('SOLR_HOST', 'localhost'),
                'port' => env('SOLR_PORT', '8983'),
                'path' => env('SOLR_PATH', '/'),
                'core' => 'document',
            ],
            'resource' => 'DocumentSolrResource',
            'classHandler' => 'DocumentHandler',
            'accepts_types' => [ResourceType::document]
        ]
    ],
    'solr_validators_folder' => env('SOLR_VALIDATORS_FOLDER', ''),
    'solr_schemas_folder' => env('SOLR_SCHEMAS_FOLDER', ''),
    'facets' => [
        "course" => [
            "categories",
            "active",
            "workspaces",
            "tags",
            "internal",
            "aggregated",
            "duration",
            "isFree",
            "currency",
            "cost",
            "skills"
        ],
        "multimedia" => [
            "categories",
            "active",
            "type",
            "types",
            "tags",
            "workspaces"
        ],
        "activity" => [
            "categories",
            "active",
            "workspaces"
        ],
        "assessment" => [
            "categories",
            "active",
            "workspaces"
        ],
        "book" => [
            "categories",
            "active",
            "tags",
            "workspaces"
        ],
        "document" => [
            "category",
            "enhanced",
            "langcode",
            "entities_linked",
            "entities_non_linked"
        ]
    ]
];
