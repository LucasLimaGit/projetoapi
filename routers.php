<?php
global $routes;
$routes = array();

$routes['/login'] = '/users/login';
$routes['/users'] = '/users/new_record';
$routes['/users/{id}'] = '/users/view/:id';
$routes['/users/{id}/drink'] = '/drinks/new_record/:id';
$routes['/users/drink/ranking'] = '/drinks/ranking';