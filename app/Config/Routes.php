<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes(true);

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
$routes->setDefaultController('Users');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

$routes->get('/', 'Users::index', ['filter' => 'noauth']);
$routes->get('logout', 'Users::logout');
$routes->match(['get','post'],'register', 'Users::register', ['filter' => 'noauth']);
$routes->match(['get','post'],'profile', 'Users::profile',['filter' => 'auth']);
$routes->match(['get','post'],'admin/users', 'Users::viewUsers',['filter' => 'auth']);
$routes->post('admin/users/updateStatus', 'Users::updateAdminStatus',['filter' => 'auth']);
$routes->match(['get','post'],'admin/settings', 'Settings::index',['filter' => 'auth']);
$routes->post('admin/settings/addEnums', 'Settings::addEnums',['filter' => 'auth']);
$routes->post('admin/settings/updateRequirementValues', 'Settings::updateRequirementValues',['filter' => 'auth']);

$routes->get('dashboard', 'Dashboard::index',['filter' => 'auth']);
$routes->get('dashboard/getStats', 'Dashboard::getStats',['filter' => 'auth']);

$routes->get('projects', 'Projects::index',['filter' => 'auth']);
$routes->match(['get','post'],'projects/add', 'Projects::add',['filter' => 'auth']);
// $routes->match(['get','post'],'projects/add/(:num)', 'Projects::add',['filter' => 'auth']);
// $routes->match(['get','post'],'projects/delete/(:num)', 'Projects::delete',['filter' => 'auth']);

$routes->get('documents-master', 'DocumentsMaster::index',['filter' => 'auth']);
$routes->match(['get','post'],'documents-master/add', 'DocumentsMaster::add',['filter' => 'auth']);
$routes->match(['get','post'],'documents-master/delete', 'DocumentsMaster::delete',['filter' => 'auth']);

$routes->get('documents-acronyms', 'Acronyms::index',['filter' => 'auth']);
$routes->match(['get','post'],'documents-acronyms/add', 'Acronyms::add',['filter' => 'auth']);
$routes->match(['get','post'],'documents-acronyms/add/(:num)', 'Acronyms::add',['filter' => 'auth']);
$routes->match(['get','post'],'documents-acronyms/delete/(:num)', 'Acronyms::delete',['filter' => 'auth']);

$routes->get('team', 'Team::index',['filter' => 'auth']);
$routes->match(['get','post'],'team/add', 'Team::add',['filter' => 'auth']);
$routes->match(['get','post'],'team/add/(:num)', 'Team::add',['filter' => 'auth']);
$routes->match(['get','post'],'team/delete/(:num)', 'Team::delete',['filter' => 'auth']);

$routes->get('risk-assessment', 'RiskAssessment::index',['filter' => 'auth']);
$routes->match(['get','post'],'risk-assessment/add', 'RiskAssessment::add',['filter' => 'auth']);
$routes->match(['get','post'],'risk-assessment/delete', 'RiskAssessment::delete',['filter' => 'auth']);

$routes->get('requirements', 'Requirements::index',['filter' => 'auth']);
$routes->match(['get','post'],'requirements/add', 'Requirements::add',['filter' => 'auth']);
$routes->match(['get','post'],'requirements/add/(:num)', 'Requirements::add',['filter' => 'auth']);
$routes->match(['get','post'],'requirements/delete', 'Requirements::delete',['filter' => 'auth']);

$routes->get('test-cases', 'TestCases::index',['filter' => 'auth']);
$routes->match(['get','post'],'test-cases/add', 'TestCases::add',['filter' => 'auth']);
$routes->match(['get','post'],'test-cases/add/(:num)', 'TestCases::add',['filter' => 'auth']);
$routes->match(['get','post'],'test-cases/delete/(:num)', 'TestCases::delete',['filter' => 'auth']);

$routes->get('traceability-matrix', 'TraceabilityMatrix::index',['filter' => 'auth']);
$routes->match(['get','post'],'traceability-matrix/add', 'TraceabilityMatrix::add',['filter' => 'auth']);
$routes->match(['get','post'],'traceability-matrix/add/(:num)', 'TraceabilityMatrix::add',['filter' => 'auth']);
$routes->match(['get','post'],'traceability-matrix/delete/(:num)', 'TraceabilityMatrix::delete',['filter' => 'auth']);
$routes->match(['get','post'],'traceability-matrix/getIDDescription/(:num)/(:num)', 'TraceabilityMatrix::getIDDescription',['filter' => 'auth']);

$routes->match(['get','post'],'generate-documents/downloadDocuments/(:num)/(:num)', 'GenerateDocuments::downloadDocuments',['filter' => 'auth']);
$routes->match(['get','post'],'generate-documents/checkGenerateDocuments/(:num)', 'GenerateDocuments::checkGenerateDocuments',['filter' => 'auth']);
$routes->match(['get','post'],'generate-documents/updateGenerateDocumentPath/(:num)', 'GenerateDocuments::updateGenerateDocumentPath',['filter' => 'auth']);

$routes->get('reviews', 'Reviews::index',['filter' => 'auth']);
$routes->get('reviews/getReviews', 'Reviews::getReviews', ['filter' => 'auth']);
$routes->get('reviews/getReviewStats', 'Reviews::getReviewStats', ['filter' => 'auth']);
$routes->match(['get','post'],'reviews/add', 'Reviews::add',['filter' => 'auth']);
$routes->match(['get','post'],'reviews/add/(:num)', 'Reviews::add',['filter' => 'auth']);
$routes->match(['get','post'],'reviews/delete/(:num)', 'Reviews::delete',['filter' => 'auth']);
$routes->post('reviews/addDocReview', 'Reviews::addDocReview',['filter' => 'auth']);
$routes->post('reviews/saveComment', 'Reviews::saveComment',['filter' => 'auth']);
$routes->post('reviews/deleteComment', 'Reviews::deleteComment',['filter' => 'auth']);

$routes->get('documents', 'Documents::index',['filter' => 'auth']);
$routes->get('documents/getDocuments', 'Documents::getDocuments', ['filter' => 'auth']);
$routes->get('documents/getDocumentStats', 'Documents::getDocumentStats', ['filter' => 'auth']);
$routes->get('documents/add', 'Documents::add',['filter' => 'auth']);
$routes->post('documents/save', 'Documents::save',['filter' => 'auth']);
$routes->match(['get','post'],'documents/delete/(:num)', 'Documents::delete',['filter' => 'auth']);

//This should be removed
$routes->match(['get','post'],'documents/add/(:num)', 'Documents::add',['filter' => 'auth']);


$routes->get('documents-templates', 'DocumentTemplate::index',['filter' => 'auth']);
$routes->post('documents-templates/addTemplate', 'DocumentTemplate::addTemplate',['filter' => 'auth']);
$routes->match(['get','post'],'documents-templates/add', 'DocumentTemplate::add',['filter' => 'auth']);
$routes->match(['get','post'],'documents-templates/add/(:num)', 'DocumentTemplate::add',['filter' => 'auth']);
$routes->match(['get','post'],'documents-templates/delete/(:num)', 'DocumentTemplate::delete',['filter' => 'auth']);

$routes->get('inventory-master', 'InventoryMaster::index',['filter' => 'auth']);
$routes->match(['get','post'],'inventory-master/add', 'InventoryMaster::add',['filter' => 'auth']);
$routes->match(['get','post'],'inventory-master/add/(:num)', 'InventoryMaster::add',['filter' => 'auth']);
$routes->match(['get','post'],'inventory-master/delete/(:num)', 'InventoryMaster::delete',['filter' => 'auth']);

$routes->get('bulk-insert', 'BulkInsert::index', ['filter' => 'auth']);

$routes->get('taskboard', 'Taskboard::index',['filter' => 'auth']);
$routes->get('taskboard/getTasks', 'Taskboard::getTasks',['filter' => 'auth']);
$routes->post('taskboard/addTask', 'Taskboard::addTask',['filter' => 'auth']);
$routes->post('taskboard/addComment', 'Taskboard::addComment',['filter' => 'auth']);
$routes->post('taskboard/deleteTask', 'Taskboard::deleteTask',['filter' => 'auth']);
$routes->post('taskboard/updateTaskColumn', 'Taskboard::updateTaskColumn',['filter' => 'auth']);

$routes->get('diagramsList', 'Diagram::index',['filter' => 'auth']);
$routes->get('diagrams/getDiagrams', 'Diagram::getDiagrams',['filter' => 'auth']);
$routes->get('diagrams/draw', 'Diagram::draw',['filter' => 'auth']);
$routes->post('diagrams/save', 'Diagram::save',['filter' => 'auth']);
$routes->post('diagrams/delete', 'Diagram::delete',['filter' => 'auth']);


$routes->get('actionList', 'ActionList::index',['filter' => 'auth']);
$routes->post('actionList/update', 'ActionList::update',['filter' => 'auth']);
$routes->post('actionList/delete', 'ActionList::delete',['filter' => 'auth']);
/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need to it be able to override any defaults in this file. Environment
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
