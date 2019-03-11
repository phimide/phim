<?php
return [
    'search' => [
        'service' => 'Search',
        'description' => 'Search words in a project',
        'options' => [
            'words:w' => [
                'description' => 'Words to search',
                'require' => true
            ],
            'file:f' => [
                'description' => 'The file containing the words to search',
                'require' => true
            ],
            'line:l' => [
                'description' => 'The line containing the words to search',
                'require' => true
            ]
        ]
    ]
];
