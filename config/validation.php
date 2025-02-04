<?php

return [
    'verify_code' => [
        'length' => 6,
        'min_value' => 100000,
        'max_value' => 999999
    ],
    'expires_otp' => 60,
    'max_length_uuid' => 36,
    'email' => [
        'min_length' => 6,
        'max_length' => 255
    ],
    'username' => [
        'min_length' => 6,
        'max_length' => 50
    ],
    'password' => [
        'min_length' => 8,
        'max_length' => 50
    ],
    'day' => [
        'min_value' => 1,
        'max_value' => 31
    ],
    'month' => [
        'min_value' => 1,
        'max_value' => 12
    ],
    'year' => [
        'min_value' => 1905
    ],
    'gender' => [
        'men' => 0,
        'female' => 1
    ],
    'token' => [
        'max_length' => 255
    ],
    'type_account' => [
        'system' => 0,
        'organization' => 1,
        'child' => 2,
        'parent' => 3
    ],
    'identity_id' => [
        'length' => 12
    ],
    'user_code' => [
        'length' => 8
    ],
    'gamer' => [
        'name' => [
            'max_length' => 255
        ],
    ]
];
