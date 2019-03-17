<?php

$config = [];

$config['commands'] = [
    'project.init' => [
        'service' => 'Initialize',
        'description' => 'Initialize the project',
        'options' => [
            'project' => [
                'description' => 'Project Information',
                'require' => true
            ],

        ]
    ],

    'project.search' => [
        'service' => 'Search',
        'description' => 'Search words in a project',
        'options' => [
            'projecthash' => [
                'description' => 'Project Hash',
                'require' => true
            ],
            'file' => [
                'description' => 'The file containing the words to search',
                'require' => true
            ],
            'line' => [
                'description' => 'The line containing the words to search',
                'require' => true
            ],
            'pos' => [
                'description' => 'The position of the cursor',
                'require' => true
            ]
        ]
    ]
];

$config['dataRoot'] = "/usr/local/phim/projects";

$config['serialize_handler'] = 'serialize';
$config['unserialize_handler'] = 'unserialize';

return $config;
