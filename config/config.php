<?php
$config = [];

$commands['project.gethash'] = [
    'service' => 'GetHash',
    'description' => 'Get the project hash',
    'options' => [
        'project' => [
            'description' => 'Project Information',
            'require' => true
        ],
    ]
];

$commands['project.init'] = [
    'service' => 'Initialize',
    'description' => 'Initialize the project',
    'options' => [
        'project' => [
            'description' => 'Project Information',
            'require' => true
        ],
    ]
];

$commands['project.search'] = [
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
];

$config['commands'] = $commands;
$config['dataRoot'] = "/usr/local/phim/projects";
$config['profilePath'] = "/usr/local/phim";

return $config;
