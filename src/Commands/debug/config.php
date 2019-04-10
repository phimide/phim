<?php
return [
    'service' => 'Debug',
    'description' => 'Start Debugging and setting up breakpoints',
    'options' => [
        'file' => [
            'description' => 'The file to set breakpoint at',
            'require' => true
        ],
        'line' => [
            'description' => 'The line number to set breakpoint at',
            'require' => true
        ],
        'cmd' => [
            'description' => 'The command to run',
            'require' => true
        ],
        'variable' => [
            'description' => 'The variable to inspect',
            'require' => false
        ],
        'hardbreak' => [
            'description' => 'Whether we want to hardbreak on breakpoint or not',
            'require' => false
        ],
        'depth' => [
            'description' => 'The maximum depth for variable inspection',
            'require' => false
        ],
        'output' => [
            'description' => 'The file containing the debugging details',
            'require' => true
        ]
    ]
];
