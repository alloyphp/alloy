<?php
// Configuration
$cfg = array();
$cfg['env']['https'] = (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') ? false : true;

$cfg['path']['root'] = dirname(dirname(__DIR__));

// Directories
$cfg['dir']['app'] = '/app/';
$cfg['dir']['config'] = $cfg['dir']['app'] . 'config/';
$cfg['dir']['www'] = '/www/';
$cfg['dir']['assets'] = $cfg['dir']['www'] . 'assets/';
$cfg['dir']['lib'] = '/lib/';
$cfg['dir']['vendor'] = '/vendor/';
$cfg['dir']['layouts'] = $cfg['dir']['app'] . 'layouts/';

// Full root paths
$cfg['path']['app'] = dirname(__DIR__);
$cfg['path']['config'] = __DIR__;
$cfg['path']['www'] = $cfg['path']['root'] . $cfg['dir']['www'];
$cfg['path']['lib'] = $cfg['path']['root'] . $cfg['dir']['lib'];
$cfg['path']['vendor'] = $cfg['path']['root'] . $cfg['dir']['vendor'];
$cfg['path']['layouts'] = $cfg['path']['root'] . $cfg['dir']['layouts'];

// URLs
$cfg['url']['request'] = (isset($_GET['url']) ? urldecode($_GET['url']) : '' );
$cfg['url']['root'] = 'http' . (($cfg['env']['https']) ? 's' : '' ) . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/' . str_replace('\\', '/', substr($cfg['path']['root'] . $cfg['dir']['www'], strlen($_SERVER['DOCUMENT_ROOT'])+1));
$cfg['url']['assets'] = $cfg['url']['root'] . str_replace($cfg['dir']['www'], '', $cfg['dir']['assets']);

// Use Apache/IIS rewrite on URLs?
$cfg['url']['rewrite'] = true;

// Debug?
$cfg['debug'] = false;

// In Development Mode?
$cfg['mode']['development'] = true;

// Plugins loaded
$cfg['plugins'] = array(
    'Alloy_Layout', # app/Plugin/Alloy/Layout
    'Spot' # vendor/Plugin/Spot
);

// Layout to wrap around response (if Alloy_Layout plugin enabled)
$cfg['layout'] = array(
    'enabled' => true,
    'template' => 'app'
);

// Database (Optional - only used if module loads a mapper)
$cfg['database']['master'] = array(
    'adapter' => 'mysql',
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'alloy',
    'options' => array(
        PDO::ERRMODE_EXCEPTION => true,
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_EMULATE_PREPARES => true
    )
);

// Session Settings
$cfg['session']['lifetime'] = 28000;

// Locale Settings
$cfg['i18n'] = array(
    'charset' => 'UTF-8',
    'language' => 'en_US',
    'timezone' => 'America/Chicago'
);

return $cfg;