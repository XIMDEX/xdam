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
        'document'  => [
            'endpoint'      => [
                'scheme'    => 'http', # or https
                'host'      => env('SOLR_HOST', 'localhost'),
                'port'      => env('SOLR_PORT', '8983'),
                'path'      => env('SOLR_PATH', '/'),
                'core'      => 'document',
            ],
            'resource'      => 'DocumentSolrResource',
            'classHandler'  => 'DocumentHandler',
            'accepts_types' => [ResourceType::document]
        ],
        'lom'   => [
            'endpoint'      => [
                'scheme'    => 'http', # or https
                'host'      => env('SOLR_HOST', 'localhost'),
                'port'      => env('SOLR_PORT', '8983'),
                'path'      => env('SOLR_PATH', '/'),
                'core'      => 'lom',
            ],
            'resource'      => 'LOMSolrResource',
            'classHandler'  => 'LOMHandler'
        ],
        'lomes' => [
            'endpoint'      => [
                'scheme'    => 'http', # or https
                'host'      => env('SOLR_HOST', 'localhost'),
                'port'      => env('SOLR_PORT', '8983'),
                'path'      => env('SOLR_PATH', '/'),
                'core'      => 'lomes',
            ],
            'resource'      => 'LOMSolrResource',
            'classHandler'  => 'LOMHandler'
        ]
    ],
    'solr_validators_folder'    => env('SOLR_VALIDATORS_FOLDER', ''),
    'solr_schemas_folder'       => env('SOLR_SCHEMAS_FOLDER', ''),
    'solr_core_allow_external'  => explode(',', env('SOLR_CORES_ALLOW', 'multimedia,activity,assessment,book,course'))
];
