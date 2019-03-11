<?php
return [
    'search' => [
        'service' => 'Search',
        'description' => 'Search words in a project',
        'options' => [
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
