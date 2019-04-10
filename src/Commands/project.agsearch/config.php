<?php
return [
    'service' => 'AgSearch',
    'description' => 'Perform word search in a project using ag',
    'options' => [
        'project' => [
            'description' => 'Project Information',
            'require' => true
        ],
        'word' => [
            'description' => 'The word to search in a project',
            'require' => true
        ],
    ]
];
