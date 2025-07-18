<?php

return [
    [
        'role_code' => '01',
        'large_function' => [
            [
                'name' => 'dashboard',
                'function_code' => 'dashboard.management',
                'description' => 'Dashboard Management',
                'permission' => \App\Enums\User\ActionEnum::VIEW->value,
                'children' => []
            ],
            [
                'name' => 'candidate management',
                'function_code' => 'candidate.management',
                'description' => 'Candidate Management',
                'permission' => \App\Enums\User\ActionEnum::VIEW->value,
                'children' => [
                    [
                        'name' => 'candidate list',
                        'function_code' => 'candidate.list',
                        'description' => 'Candidate List',
                        'permission' => \App\Enums\User\ActionEnum::NONE->value,
                    ],
                    [
                        'name' => 'Basic information',
                        'function_code' => 'candidate.basicInfo',
                        'description' => 'Basic information',
                        'permission' => \App\Enums\User\ActionEnum::VIEW->value,
                    ]
                ]
            ]
        ],
    ]
];
