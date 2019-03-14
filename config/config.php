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
        'description' => 'create indexes for the classes, traits, interfaces, functions in a project',
        'options' => [
            'project' => [
                'description' => 'Project Information',
                'require' => true
            ]
        ]
    ]
];

$config['dataRoot'] = "~/.vim/tmp";
$config['cache'] = [
    'type' => 'redis',
    'host' => '127.0.0.1',
    'port' => 6379
];
$config['log'] = "~/.vim/tmp/phim.log";

return $config;
