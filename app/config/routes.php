<?php
/**
 * User login/logout actions
 */
$router->route('login', '/login')
	->defaults(array('module' => 'User_Session', 'action' => 'new', 'format' => 'html'))
	->post(array('action' => 'post'));

$router->route('logout', '/logout')
	->defaults(array('module' => 'User_Session', 'action' => 'delete', 'format' => 'html'));


/**
 * Default routes
 */
$router->route('module_item_action', '/<:module>/<#item>/<:action>(.<:format>)') // :format optional
	->defaults(array('format' => 'html'));

$router->route('module_item', '/<:module>/<#item>(.<:format>)') // :format optional
	->defaults(array('action' => 'view', 'format' => 'html'))
	->get(array('action' => 'view'))
	->post(array('action' => 'post'))
	->put(array('action' => 'put'))
	->delete(array('action' => 'delete'));
	
$router->route('module', '/<:module>(.<:format>)') // :format optional
	->defaults(array('action' => 'index', 'format' => 'html'));

$router->route('default', '/')
	->defaults(array('module' => 'Home', 'action' => 'index', 'format' => 'html'));
