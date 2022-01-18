<?php

use App\Enums\InesjaType;

return [
    'dataset' => [
        'asunto' => [
            'category' => InesjaType::Asunto,
            'fields' => [
                'id' => '_id',
                'uuid' => '_source.data.uuid',
                'language' => '_source.data.langcode',
                'title' => '_source.data.title',
                'body' => '_source.data.field_resumen',
                'image' => '_source.data.field_img.field_img.thumbnail.uri',
                'external_url' => '_source.data.path.alias'
            ],
            'search' => [
                '_source' => 'data.uuid,data.field_resumen,data.title,data.path,data.langcode,data.field_img,data.moderation_state'
            ]
        ],
        'noticia' => [
            'category' => InesjaType::Noticia,
            'fields' => [
                'id' => '_id',
                'uuid' => '_source.data.uuid',
                'language' => '_source.data.langcode',
                'title' => '_source.data.title',
                'body' => '_source.data.field_noticia_texto_completo',
                'image' => '_source.data.field_noticia_fotografia.thumbnail.uri',
                'external_url' => '_source.data.path.alias'
            ],
            'search' => [
                '_source' => 'data.uuid,data.field_noticia_fotografia,data.field_noticia_texto_completo,data.title,data.path,data.langcode,data.field_img,data.moderation_state'
            ]
        ]
    ],
    'search_fields' => [
        'size',
        'from',

    ],
    'search_sort' => 'sort=date:desc',
    'search_only_published' => 'q=data.moderation_state:"published"',
    'base_url' => 'https://www.juntadeandalucia.es/ssdigitales/datasets/contentapi/1.0.0/search/',
    'field_result' => 'resultado',
    'url' => 'https://www.juntadeandalucia.es'
];