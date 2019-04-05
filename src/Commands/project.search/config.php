<?php
return [
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
