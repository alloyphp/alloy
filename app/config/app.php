<?php
// Configuration
$cfg = array();
$cfg['env']['https'] = (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') ? false : true;

$cfg['path']['root'] = dirname(dirname(__DIR__));

$cfg['dir']['app'] = '/app/';
$cfg['dir']['config'] = $cfg['dir']['app'] . 'config/';
$cfg['dir']['www'] = '/www/';
$cfg['dir']['assets'] = $cfg['dir']['www'] . 'assets/';
$cfg['dir']['assets_admin'] = $cfg['dir']['assets'] . 'admin/';
$cfg['dir']['lib'] = '/lib/';
$cfg['dir']['modules'] = $cfg['dir']['app'];
$cfg['dir']['layouts'] = $cfg['dir']['app'] . 'layouts/';

$cfg['path']['app'] = dirname(__DIR__);
$cfg['path']['config'] = __DIR__;
$cfg['path']['lib'] = $cfg['path']['root'] . $cfg['dir']['lib'];
$cfg['path']['modules'] = $cfg['path']['root'] . $cfg['dir']['modules'];
$cfg['path']['public'] = $cfg['path']['root'] . $cfg['dir']['www'];
$cfg['path']['layouts'] = $cfg['path']['root'] . $cfg['dir']['layouts'];

// URLs
$cfg['url']['request'] = (isset($_GET['url']) ? urldecode($_GET['url']) : '' );
$cfg['url']['root'] = 'http' . (($cfg['env']['https']) ? 's' : '' ) . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/' . str_replace('\\', '/', substr($cfg['path']['root'] . $cfg['dir']['www'], strlen($_SERVER['DOCUMENT_ROOT'])+1));
$cfg['url']['assets'] = $cfg['url']['root'] . str_replace($cfg['dir']['www'], '', $cfg['dir']['assets']);
$cfg['url']['assets_admin'] = $cfg['url']['root'] . str_replace($cfg['dir']['www'], '', $cfg['dir']['assets_admin']);

// Use Apache/IIS rewrite on URLs?
$cfg['url']['rewrite'] = true;

// Debug?
$cfg['debug'] = true;

// In Development Mode?
$cfg['mode']['development'] = true;

// Database (Optional - only used if module loads a mapper)
$cfg['database']['master']['adapter'] = 'mysql';
$cfg['database']['master']['host'] = 'localhost';
$cfg['database']['master']['username'] = 'root';
$cfg['database']['master']['password'] = '';
$cfg['database']['master']['database'] = 'alloy';
$cfg['database']['master']['options'] = array(
    PDO::ERRMODE_EXCEPTION => true,
    PDO::ATTR_PERSISTENT => false,
    PDO::ATTR_EMULATE_PREPARES => true
    );

// Session Settings
$cfg['session']['lifetime'] = 28000;

// Locale Settings
$cfg['i18n']['charset'] = 'UTF-8';
$cfg['i18n']['language'] = 'en_US';
$cfg['i18n']['timezone'] = 'America/Chicago';

return $cfg;