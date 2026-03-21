<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('api/health', 'Api\Health::index');
$routes->post('api/weather', 'Api\Weather::forecast');
