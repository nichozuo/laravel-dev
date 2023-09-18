<?php

return [
    'perPageAllow' => [10, 20, 50, 100],
    'dbBackupList' => [
        'sys_permissions',
        'sys_roles',
        'sys_role_has_permissions',
        'sys_model_has_roles',
        'personal_access_tokens',
    ],
    'hasApiTokens' => ['admins'],
    'hasRoles' => ['sys_permissions', 'sys_roles', 'admins'],
    'hasNodeTrait' => ['sys_permissions'],
    'tablePrefix' => '',
    'showDoc' => env('SHOW_DOC', true),
];
