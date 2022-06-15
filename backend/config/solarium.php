<?php

use App\Enums\ResourceType;

use App\Http\Resources\Solr\ActivitySolrResource;
use App\Http\Resources\Solr\AssessmentSolrResource;
use App\Http\Resources\Solr\CourseSolrResource;
use App\Http\Resources\Solr\MultimediaSolrResource;
use App\Http\Resources\Solr\BookSolrResource;

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
            'resource' => className(ActivitySolrResource::class),
            'classHandler' => 'ActivityHandler',
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
            'resource' => className(AssessmentSolrResource::class),
            'classHandler' => 'AssessmentHandler',
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
            'resource' => className(CourseSolrResource::class),
            'classHandler' => 'CourseHandler',
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
            'resource' => className(MultimediaSolrResource::class),
            'classHandler' => 'MultimediaHandler',
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
            'resource' => className(BookSolrResource::class),
            'classHandler' => 'BookHandler',
            'accepts_types' => [ResourceType::book]
        ]
    ],
    'solr_validators_folder' => env('SOLR_VALIDATORS_FOLDER', ''),
    'solr_schemas_folder' => env('SOLR_SCHEMAS_FOLDER', ''),

];


function className(string $className): string
{
    $class_parts = explode('\\', $className);
    return end($class_parts);
}