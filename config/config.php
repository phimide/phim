<?php

$config = [];

$config['commands'] = [
    'project.search' => [
        'service' => 'Search',
        'description' => 'Search words in a project',
        'options' => [
            'project' => [
                'description' => 'Project Information',
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
    ],

    'project.createindex' => [
        'service' => 'CreateIndex',
        'description' => 'Index the classes, traits, interfaces, functions in a project',
        'options' => [
            'project' => [
                'description' => 'Project Information',
                'require' => true
            ]
        ]
    ]
];

$config['dataDir'] = "/tmp/phim/data";

return $config;
