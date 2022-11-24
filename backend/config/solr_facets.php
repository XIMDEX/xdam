<?php

return [
    'lom'           => [
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
    'lomes'         => [],
    'tika_metadata' => [
        'keywords',
        'language',
        'pages',
        'words',
        'width',
        'height',
        'lossless',
        'mime'
    ],
    'constants'     => [
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
    ]
];