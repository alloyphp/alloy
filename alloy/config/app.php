<?php
// Configuration
$alloy = array();

// Directories
$alloy['dir']['root'] = '/';
$alloy['dir']['config'] = $alloy['dir']['root'] . 'config/';
$alloy['dir']['lib'] = $alloy['dir']['root'] . 'lib/';

// Full root paths
$alloy['path']['root'] = dirname(__DIR__);
$alloy['path']['config'] = __DIR__;
$alloy['path']['lib'] = $alloy['path']['root'] . $alloy['dir']['lib'];

return array('alloy' => $alloy);