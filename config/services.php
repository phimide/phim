<?php
return [
    'project.search' => [
        'service' => 'Search',
        'description' => 'Search words in a project',
        'options' => [
            'project' => [
                'description' => 'Project Information',
                'require' => true
            ],
            'words' => [
                'description' => 'Words to search',
                'require' => true
            ],
            'file' => [
                'description' => 'The file containing the words to search',
                'require' => true
            ],
            'line' => [
                'description' => 'The line containing the words to search',
                'require' => true
            ]
        ]
    ]
];
