<?php
// Configuration
$cfg = require dirname(dirname(__DIR__)) . '/alloy/config/app.php';
$alloy = $cfg['alloy'];
$app = array();

// Directories (from install root)
$app['dir']['root'] = '/';
$app['dir']['config'] = $app['dir']['root'] . 'config/';
$app['dir']['www'] = $app['dir']['root'] . 'www/';
$app['dir']['assets'] = $app['dir']['www'] . 'assets/';
$app['dir']['lib'] = $app['dir']['root'] . 'lib/';
$app['dir']['layouts'] = $app['dir']['root'] . 'layouts/';

// Full root paths
$app['path']['root'] = dirname(__DIR__);
$app['path']['config'] = __DIR__;
$app['path']['www'] = $app['path']['root'] . $app['dir']['www'];
$app['path']['lib'] = $app['path']['root'] . $app['dir']['lib'];
$app['path']['layouts'] = $app['path']['root'] . $app['dir']['layouts'];

// URLs
$isHttps = (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') ? false : true;
$cfg['url']['root'] = 'http' . (($isHttps) ? 's' : '' ) . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/' . str_replace('\\', '/', substr($app['path']['root'] . $app['dir']['www'], strlen($_SERVER['DOCUMENT_ROOT'])+1));
$cfg['url']['assets'] = $cfg['url']['root'] . str_replace($app['dir']['www'], '', $app['dir']['assets']);

// Use Apache/IIS/nginx rewrite on URLs?
$cfg['url']['rewrite'] = true;

// Autoload libs
$app['autoload']['namespaces'] = array(
    'Alloy' => $alloy['path']['lib'],
    'App' => $app['path']['lib'],
    'Module' => array($app['path']['root'], $alloy['path']['root']),
    'Plugin' => array($app['path']['root'], $alloy['path']['root']),
);
$app['autoload']['prefixes'] = array(
    'Zend_' => $app['path']['lib']
);

// Plugins loaded
$app['plugins'] = array(
    'Alloy_Layout', # app/Plugin/Alloy/Layout
    'Spot' # vendor/Plugin/Spot
);

// Layout to wrap around response (if Alloy_Layout plugin enabled)
$app['layout'] = array(
    'enabled' => true,
    'template' => 'app'
);

// Debug?
$app['debug'] = false;

// In Development Mode?
$app['mode']['development'] = true;

// Database (Optional - only used if your app uses it)
$app['database']['master'] = array(
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
$app['session']['lifetime'] = 28000;

// Locale Settings
$app['i18n'] = array(
    'charset' => 'UTF-8',
    'language' => 'en_US',
    'timezone' => 'America/Chicago',
    'date_format' => 'M d, Y',
    'time_format' => 'H:i:s'
);

return $cfg + array('app' => $app);