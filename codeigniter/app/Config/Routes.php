<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('User');
$routes->setDefaultMethod('');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'User::index');
$routes->get('registration', 'User::showRegistration');
$routes->get('fallback', 'Fallback::index');
$routes->get('people', 'People::index');
$routes->get('getPeople', 'People::getPeople');
$routes->get('addPerson', 'People::addPerson');
$routes->get('editPerson', 'People::editPerson');
$routes->get('logout', 'User::logout');
$routes->match(['post'], 'register', 'User::register');
$routes->match(['post'], 'login', 'User::login');
$routes->match(['post'], 'addPersonValidation', 'People::addPerson_Validation');
$routes->match(['post'], 'editPersonValidation', 'People::editPerson_Validation');
$routes->match(['post'], 'deletePerson', 'People::deletePerson');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
