<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('v1', ['namespace' => 'App\Controllers\Api\V1'], function ($routes) {
    
    // Auth Routes
    $routes->group('auth', function ($routes) {
        $routes->post('register', 'Auth::register');
        $routes->post('login', 'Auth::login');
        $routes->post('logout', 'Auth::logout');
        $routes->post('refresh-tokens', 'Auth::refreshTokens');
        $routes->post('forgot-password', 'Auth::forgotPassword');
        $routes->post('reset-password', 'Auth::resetPassword');
        
        // Protected Auth Routes
        $routes->post('send-verification-email', 'Auth::sendVerificationEmail', ['filter' => 'jwt']);
        $routes->post('verify-email', 'Auth::verifyEmail');
    });

    // User Routes
    $routes->group('users', ['filter' => 'jwt'], function ($routes) {
        // Admin only: Create User & Get All Users
        $routes->post('/', 'Users::create', ['filter' => 'role:manageUsers']);
        $routes->get('/', 'Users::index', ['filter' => 'role:getUsers']);
        
        // Protected: Get, Update, Delete specific user
        $routes->get('(:segment)', 'Users::show/$1', ['filter' => 'role:getUsers']);
        $routes->patch('(:segment)', 'Users::update/$1', ['filter' => 'role:manageUsers']);
        $routes->delete('(:segment)', 'Users::delete/$1', ['filter' => 'role:manageUsers']);
    });

    // Documentation
    $routes->get('docs', 'Docs::index'); 
});