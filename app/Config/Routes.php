<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'AuthController::loginForm');
$routes->get('/login', 'AuthController::loginForm');
$routes->post('/login', 'AuthController::login');
$routes->post('/logout', 'AuthController::logout');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/dashboard', 'DashboardController::index');
    $routes->get('/products', 'ProductsController::index');
    $routes->match(['get', 'post'], '/products/new', 'ProductsController::save');
    $routes->match(['get', 'post'], '/products/edit/(:num)', 'ProductsController::save/$1');
    $routes->post('/products/image/(:num)', 'ProductsController::uploadImage/$1');
    $routes->post('/products/delete/(:num)', 'ProductsController::delete/$1');

    $routes->get('/customers', 'CustomersController::index');
    $routes->match(['get', 'post'], '/customers/new', 'CustomersController::save');
    $routes->match(['get', 'post'], '/customers/edit/(:num)', 'CustomersController::save/$1');
    $routes->post('/customers/delete/(:num)', 'CustomersController::delete/$1');

    $routes->get('/users', 'UsersController::index');
    $routes->match(['get', 'post'], '/users/new', 'UsersController::save');
    $routes->match(['get', 'post'], '/users/edit/(:num)', 'UsersController::save/$1');
    $routes->post('/users/delete/(:num)', 'UsersController::delete/$1');

    $routes->get('/sales', 'SalesController::index');
    $routes->match(['get', 'post'], '/sales/new', 'SalesController::save');
});
