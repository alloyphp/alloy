<?php
/**
* Default routes
*/
$router->route('module_item_action', '/<:module>/<#item>/<:action>(.<:format>)') // :format optional
    ->defaults(array('format' => 'html'));

$router->route('module_item', '/<:module>/<#item>(.<:format>)') // :format optional
    ->defaults(array('action' => 'view', 'format' => 'html'))
    ->get(array('action' => 'view'))
    ->put(array('action' => 'put'))
    ->delete(array('action' => 'delete'));

$router->route('module_action', '/<:module>/<:action>(.<:format>)') // :format optional
    ->defaults(array('format' => 'html'))
    ->post(array('action' => 'post'));

$router->route('module', '/<:module>(.<:format>)') // :format optional
    ->defaults(array('action' => 'index', 'format' => 'html'));

$router->route('default', '/')
    ->defaults(array('module' => 'Home', 'action' => 'index', 'format' => 'html'));
