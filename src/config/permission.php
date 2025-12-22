<?php

use DateInterval;
use Spatie\Permission\Middlewares\AuthenticatedSession;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;

return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
        'teams' => 'teams',
    ],

    'column_names' => [
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'team_id',
    ],

    'register_permission_check_method' => false,

    'ignore_super_admin_role' => false,

    'display_permission_in_exception' => false,

    'display_role_in_exception' => false,

    'enable_wildcard_permission' => false,

    'enable_permission_expiration' => false,

    'teams' => false,

    'default_guard' => 'web',

    'default_wildcard_permission_case_sensitive' => false,

    'cache' => [
        'expiration_time' => DateInterval::createFromDateString('24 hours'),

        'key' => 'spatie.permission.cache',

        'store' => 'default',
    ],

    'permission' => PermissionMiddleware::class,
    'role' => RoleMiddleware::class,
    'authenticated_session' => AuthenticatedSession::class,
];
