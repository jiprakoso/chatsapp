<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// Catatan: '/' dipakai Web Admin Dashboard (lihat grup webadminauth di bawah).

$routes->group('api/auth', static function ($routes) {
    $routes->post('register', 'Api\AuthController::register');
    $routes->post('login', 'Api\AuthController::login');
});

// Web Admin Auth Routes (no JWT filter for login page)
$routes->get('login', 'WebAdmin\AuthController::login');
$routes->post('login', 'WebAdmin\AuthController::loginPost');
$routes->get('logout', 'WebAdmin\AuthController::logout');

$routes->group('api', ['filter' => 'jwtauth'], static function ($routes) {
    $routes->post('devices', 'Api\DeviceController::register');
    $routes->delete('devices/(:num)', 'Api\DeviceController::delete/$1');
});

$routes->group('api/admin', ['filter' => 'jwtauth'], static function ($routes) {
    $routes->get('roles', 'Api\Admin\RoleController::index', ['filter' => 'permission:roles.manage']);
    $routes->get('roles/(:num)', 'Api\Admin\RoleController::show/$1', ['filter' => 'permission:roles.manage']);
    $routes->post('roles', 'Api\Admin\RoleController::create', ['filter' => 'permission:roles.manage']);
    $routes->put('roles/(:num)', 'Api\Admin\RoleController::update/$1', ['filter' => 'permission:roles.manage']);
    $routes->delete('roles/(:num)', 'Api\Admin\RoleController::delete/$1', ['filter' => 'permission:roles.manage']);
    $routes->put('roles/(:num)/permissions', 'Api\Admin\RoleController::syncPermissions/$1', ['filter' => 'permission:roles.manage']);

    $routes->get('permissions', 'Api\Admin\PermissionController::index', ['filter' => 'permission:permissions.manage']);
    $routes->get('permissions/(:num)', 'Api\Admin\PermissionController::show/$1', ['filter' => 'permission:permissions.manage']);
    $routes->post('permissions', 'Api\Admin\PermissionController::create', ['filter' => 'permission:permissions.manage']);
    $routes->put('permissions/(:num)', 'Api\Admin\PermissionController::update/$1', ['filter' => 'permission:permissions.manage']);
    $routes->delete('permissions/(:num)', 'Api\Admin\PermissionController::delete/$1', ['filter' => 'permission:permissions.manage']);

    $routes->get('users', 'Api\Admin\UserController::index', ['filter' => 'permission:users.view']);
    $routes->get('users/(:num)', 'Api\Admin\UserController::show/$1', ['filter' => 'permission:users.view']);
    $routes->get('users/me', 'Api\Admin\UserController::me', ['filter' => 'permission:users.view']);
    $routes->put('users/(:num)/role', 'Api\Admin\UserController::assignRole/$1', ['filter' => 'permission:roles.manage']);
    $routes->post('users/(:num)/ban', 'Api\Admin\UserController::ban/$1', ['filter' => 'permission:users.ban']);
    $routes->post('users/(:num)/unban', 'Api\Admin\UserController::unban/$1', ['filter' => 'permission:users.ban']);
});

$routes->group('api', ['filter' => 'jwtauth'], static function ($routes) {
    $routes->post('devices', 'Api\DeviceController::register');
    $routes->delete('devices/(:num)', 'Api\DeviceController::delete/$1');

    $routes->get('conversations', 'Api\ConversationController::index');
    $routes->post('conversations', 'Api\ConversationController::create');
    $routes->get('conversations/(:num)', 'Api\ConversationController::show/$1');
    $routes->get('conversations/(:num)/messages', 'Api\MessageController::index/$1');
    $routes->post('conversations/(:num)/messages', 'Api\MessageController::store/$1');
    $routes->post('conversations/(:num)/attachments', 'Api\MessageController::storeAttachment/$1');
    $routes->post('conversations/(:num)/read', 'Api\ConversationController::markRead/$1');

    $routes->put('messages/(:num)', 'Api\MessageController::update/$1');
    $routes->delete('messages/(:num)', 'Api\MessageController::delete/$1');
    $routes->post('messages/(:num)/delivered', 'Api\MessageController::markDelivered/$1');
    $routes->post('messages/(:num)/read', 'Api\MessageController::markRead/$1');
    $routes->get('messages/(:num)/status', 'Api\MessageController::status/$1');

    $routes->get('attachments/(:num)', 'Api\AttachmentController::show/$1');
});

$routes->group('api/internal', ['filter' => 'internalauth'], static function ($routes) {
    $routes->get('devices', 'Api\Internal\DeviceController::index');
    $routes->post('devices/deactivate', 'Api\Internal\DeviceController::deactivate');
});

// Web Admin Routes (langsung di root, tanpa prefix admin)
$routes->group('', ['filter' => 'webadminauth'], static function ($routes) {
    $routes->get('/', 'WebAdmin\DashboardController::index');
    $routes->get('dashboard', 'WebAdmin\DashboardController::index');
    $routes->get('users', 'WebAdmin\UsersController::index');
    $routes->get('users/(:num)', 'WebAdmin\UsersController::view/$1');
    $routes->get('roles', 'WebAdmin\RolesController::index');
    $routes->get('roles/create', 'WebAdmin\RolesController::create');
    $routes->get('roles/edit/(:num)', 'WebAdmin\RolesController::edit/$1');
    $routes->get('permissions', 'WebAdmin\PermissionsController::index');
    $routes->get('permissions/create', 'WebAdmin\PermissionsController::create');
    $routes->get('permissions/edit/(:num)', 'WebAdmin\PermissionsController::edit/$1');
    $routes->get('conversations', 'WebAdmin\ConversationsController::index');
    $routes->get('conversations/(:num)', 'WebAdmin\ConversationsController::view/$1');
    $routes->get('settings', 'WebAdmin\SettingsController::index');
});

// API endpoints for web admin (extend existing admin API)
$routes->group('api/admin', ['filter' => 'jwtauth'], static function ($routes) {
    $routes->get('stats', 'Api\Admin\StatsController::index', ['filter' => 'permission:users.view']);
    $routes->get('activity', 'Api\Admin\StatsController::activity', ['filter' => 'permission:users.view']);
    $routes->get('conversations', 'Api\Admin\ConversationAdminController::index', ['filter' => 'permission:conversations.view_all']);
    $routes->get('conversations/(:num)', 'Api\Admin\ConversationAdminController::show/$1', ['filter' => 'permission:conversations.view_all']);
    $routes->post('cache/clear', 'Api\Admin\SystemController::clearCache', ['filter' => 'permission:system.settings']);
});
