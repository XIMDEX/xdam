<?php

return [
    'lom'       => [
        [
            'key'           => 'general_1_description',
            'subkey'        => 'Language',
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'general_1_keyword',
            'subkey'        => 'Language',
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'general_1_coverage',
            'subkey'        => 'Language',
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'general_1_structure',
            'subkey'        => 'Source',
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'general_1_aggregation_level',
            'subkey'        => 'Source',
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'meta-metadata_3_language',
            'subkey'        => null,
            'key_alias'     => 'meta_metadata_3_language',
            'subkey_alias'  => null
        ],
        [
            'key'           => 'technical_4_format',
            'subkey'        => null,
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'technical_4_other_platform_requirements',
            'subkey'        => 'Language',
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'technical_4_duration',
            'subkey'        => 'Duration',
            'key_alias'     => null,
            'subkey_alias'  => null
        ]
    ],
    'lomes'     => [
        [
            'key'           => 'meta-metadatos_3_tipo',
            'subkey'        => null,
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'uso_educativo_5_tipo_de_recurso_educativo',
            'subkey'        => null,
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'uso_educativo_5_destinatario',
            'subkey'        => null,
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'uso_educativo_5_proceso_cognitivo',
            'subkey'        => null,
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'derechos_6_derechos_de_autor_y_otras_restricciones',
            'subkey'        => null,
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'custom_1_10_accesibilidad',
            'subkey'        => null,
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'custom_1_10_nivel_educativo',
            'subkey'        => null,
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'custom_1_10_competencia',
            'subkey'        => null,
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
        [
            'key'           => 'custom_1_10_disciplina',
            'subkey'        => null,
            'key_alias'     => null,
            'subkey_alias'  => null
        ],
    ],
    'constants' => [
        'special_character' => '_',
        'key_separator'     => 3,
        'value_separator'   => 4,
        'characters_map'    => [
            [ 'from' => ' ', 'to' => 5 ],
            [ 'from' => '-', 'to' => 6 ],
            [ 'from' => '&', 'to' => 7 ],
            [ 'from' => '*', 'to' => 8 ],
            [ 'from' => '+', 'to' => 9 ],
            [ 'from' => '\'', 'to' => 10 ],
            [ 'from' => '"', 'to' => 11 ],
            [ 'from' => '¡', 'to' => 12 ],
            [ 'from' => '!', 'to' => 13 ],
            [ 'from' => '?', 'to' => 14 ],
            [ 'from' => '¿', 'to' => 15 ],
            [ 'from' => '.', 'to' => 16 ],
            [ 'from' => ':', 'to' => 17 ],
            [ 'from' => ';', 'to' => 18 ],
            [ 'from' => ',', 'to' => 19 ],
            [ 'from' => '<', 'to' => 20 ],
            [ 'from' => '>', 'to' => 21 ],
            [ 'from' => '=', 'to' => 22 ],
            [ 'from' => '@', 'to' => 23 ],
            [ 'from' => '#', 'to' => 24 ],
            [ 'from' => '%', 'to' => 25 ],
            [ 'from' => '/', 'to' => 26 ],
            [ 'from' => '$', 'to' => 27 ],
            [ 'from' => '(', 'to' => 28 ],
            [ 'from' => ')', 'to' => 29 ],
            [ 'from' => '[', 'to' => 30 ],
            [ 'from' => ']', 'to' => 31 ],
            [ 'from' => '{', 'to' => 32 ],
            [ 'from' => '}', 'to' => 33 ],
            [ 'from' => '|', 'to' => 34 ],
            [ 'from' => '≪', 'to' => 35 ],
            [ 'from' => '≫', 'to' => 36 ]
        ]
    ],
    'client' => [
        'DEFAULT' => [],
        'MHE' => [
            'meta-metadatos_3_tipo' => [
                'key'=>'3',
                'label' => 'Tipo de archivo',
                'solr_label' => 'tipo_archivo'
            ],
            'uso_educativo_5_tipo_de_recurso_educativo' => [
                'key'=>'5',
                'label' => 'Tipo de recurso',
                'solr_label' => 'tipo_recurso'
            ],
            'uso_educativo_5_destinatario' => [
                'key'=>'5',
                'label' => 'Destinatario',
                'solr_label' => 'destinatario'
            ],
            'uso_educativo_5_proceso_cognitivo' => [
                'key'=>'5',
                'label' => 'Proceso cognitivo',
                'solr_label' => 'proceso_cognitivo'
            ],
            'derechos_6_derechos_de_autor_y_otras_restricciones' => [
                'key'=>'6',
                'label' => 'Derechos de autor',
                'solr_label' => 'derechos_autor'
            ],
            'custom_1_10_accesibilidad' => [
                'key'=>'10',
                'label' => 'Accesibilidad',
                'solr_label' => 'accesibilidad'
            ],
            'custom_1_10_nivel_educativo' => [
                'key'=>'10',
                'label' => 'Nivel educativo',
                'solr_label' => 'nivel_educativo'
            ],
            'custom_1_10_competencia' => [
                'key'=>'10',
                'label' => 'Competencia',
                'solr_label' => 'competencia'
            ],
            'custom_1_10_disciplina' => [
                'key'=>'10',
                'label' => 'Disciplina',
                'solr_label' => 'disciplina'
            ],
        ]
    ],
    'lom_hidden' => [
        'DEFAULT' => [],
        'MHE' => [
            'data', 'lomes', 'lom',
            'collection', 'collections', 'workspaces', '_version_',
            'organization'
        ]
    ]
];
