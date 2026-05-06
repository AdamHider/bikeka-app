<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/SkillImporter/(:any)', 'SkillImporter::$1');

$routes->post('/Child/(:any)', 'Child::$1');
$routes->post('/Skill/(:any)', 'Skill::$1');