<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'AuthController::loginForm');
$routes->get('/login', 'AuthController::loginForm');
$routes->post('/login', 'AuthController::login');
$routes->post('/logout', 'AuthController::logout');

// Customer portal
$routes->get('/shop', 'ShopController::index');
$routes->get('/shop/products', 'ShopController::catalog');
$routes->get('/customer/login', 'CustomerAuthController::loginForm');
$routes->post('/customer/login', 'CustomerAuthController::login');
$routes->get('/customer/register', 'CustomerAuthController::registerForm');
$routes->post('/customer/register', 'CustomerAuthController::register');
$routes->post('/customer/logout', 'CustomerAuthController::logout');
$routes->post('/shop/cart/add', 'ShopController::addToCart');
$routes->get('/shop/cart', 'ShopController::cartView');
$routes->post('/shop/cart/update', 'ShopController::updateCart');
$routes->post('/shop/cart/remove/(:num)', 'ShopController::removeFromCart/$1');

$routes->group('shop', ['filter' => 'customerAuth'], static function ($routes) {
    $routes->match(['get', 'post'], 'checkout', 'ShopController::checkout');
    $routes->get('orders', 'ShopController::orders');
    $routes->get('orders/(:num)', 'ShopController::orderDetails/$1');
});
$routes->match(['get', 'post'], '/customer/profile', 'CustomerAuthController::profile', ['filter' => 'customerAuth']);

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
    $routes->get('/orders', 'OrdersController::index');
    $routes->get('/orders/(:num)', 'OrdersController::show/$1');
    $routes->post('/orders/(:num)/status', 'OrdersController::updateStatus/$1');
});
