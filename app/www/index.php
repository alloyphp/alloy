<?php
// Save current directory as full path to www
define('ALLOY_WEB_ROOT', __DIR__);

// Require app init (inital framework setup)
require dirname(__DIR__) . '/init.php';

// Init app and run
$app = new Alloy\App();
$app->run();