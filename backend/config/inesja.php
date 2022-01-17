<?php

use App\Enums\InesjaType;

return [
    'dataset' => [
        'asunto' => [
            'fields' => [
                'id' => '_id',
                'uuid' => 'data.uuid',
                'langcode' => 'data.langcode',
                'category' => InesjaType::Asunto,
                'title' => 'data.title',
                'body' => 'data.field_resumen',
                'image' => 'data.field_img.field_img',
                'external_url' => 'data.path.alias'
            ],
            'search' => [
                '_source' => ['data.uuid,data.field_resumen,data.title,data.langcode,data.moderation_state']
            ]
        ],
        'noticia' => [
            'fields' => [
                'id' => '_id',
                'uuid' => 'data.uuid',
                'langcode' => 'data.langcode',
                'category' => InesjaType::Noticia,
                'title' => 'data.title',
                'body' => 'data.field_noticia_texto_completo',
                'image' => 'data.field_noticia_fotografia',
                'external_url' => 'data.path.alias'
            ],
            'search' => [
                '_source' => ['data.uuid,data.field_resumen,data.title,data.langcode,data.moderation_state']
            ]
        ]
    ],
    'search_fields' => [
        'size',
        'from',

    ],
    'search_sort' => 'sort=date:desc',
    'search_only_published' => 'q=data.moderation_state:"published"'
];