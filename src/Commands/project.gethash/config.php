<?php
return [
    'description' => 'Get the project hash',
    'options' => [
        'project' => [
            'description' => 'Project Information',
            'require' => true
        ],
    ],
    'service' => 'GetHash',
];
