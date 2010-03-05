<?php
// Configuration
$cfg = array();
$cfg['alloy']['env']['https'] = (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') ? false : true;
$cfg['alloy']['request'] = (isset($_GET['url']) ? urldecode($_GET['url']) : '' );
$cfg['alloy']['path_root'] = dirname(dirname(__FILE__));

$cfg['alloy']['dir_www'] = '/www/';
$cfg['alloy']['dir_assets'] = $cfg['alloy']['dir_www'] . 'assets/';
$cfg['alloy']['dir_assets_admin'] = $cfg['alloy']['dir_assets'] . 'admin/';
$cfg['alloy']['dir_lib'] = '/lib/';
$cfg['alloy']['dir_modules'] = '/app/';
$cfg['alloy']['dir_themes'] = $cfg['alloy']['dir_www'] . 'themes/';

$cfg['alloy']['path_app'] = dirname(__FILE__);
$cfg['alloy']['path_lib'] = $cfg['alloy']['path_root'] . $cfg['alloy']['dir_lib'];
$cfg['alloy']['path_modules'] = $cfg['alloy']['path_root'] . $cfg['alloy']['dir_modules'];
$cfg['alloy']['path_public'] = $cfg['alloy']['path_root'] . $cfg['alloy']['dir_www'];
$cfg['alloy']['path_themes'] = $cfg['alloy']['path_root'] . $cfg['alloy']['dir_themes'];

$cfg['alloy']['url'] = 'http' . (($cfg['alloy']['env']['https']) ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'] . '/' . str_replace('\\', '/', substr($cfg['alloy']['path_root'] . $cfg['alloy']['dir_www'], strlen($_SERVER['DOCUMENT_ROOT'])+1));
$cfg['alloy']['url_themes'] = $cfg['alloy']['url'] . str_replace($cfg['alloy']['dir_www'], '', $cfg['alloy']['dir_themes']);
$cfg['alloy']['url_assets'] = $cfg['alloy']['url'] . str_replace($cfg['alloy']['dir_www'], '', $cfg['alloy']['dir_assets']);
$cfg['alloy']['url_assets_admin'] = $cfg['alloy']['url'] . str_replace($cfg['alloy']['dir_www'], '', $cfg['alloy']['dir_assets_admin']);

// Debug?
$cfg['alloy']['debug'] = false;

// In Development Mode?
$cfg['alloy']['mode']['development'] = false;

// Error Reporting
$cfg['alloy']['error_reporting'] = true;

// Use Apache/IIS rewrite on URLs?
$cfg['alloy']['url_rewrite'] = true;

// Defaults
$cfg['alloy']['default']['module'] = 'page';
$cfg['alloy']['default']['action'] = 'index';
$cfg['alloy']['default']['theme'] = 'default';
$cfg['alloy']['default']['theme_template'] = 'index';

// Database - Param names to match Zend_Config
$cfg['alloy']['database']['master']['adapter'] = 'MySQL';
$cfg['alloy']['database']['master']['host'] = 'localhost';
$cfg['alloy']['database']['master']['username'] = 'test';
$cfg['alloy']['database']['master']['password'] = 'password';
$cfg['alloy']['database']['master']['dbname'] = 'cx_cms';
$cfg['alloy']['database']['master']['options'] = array(
	PDO::ERRMODE_EXCEPTION => true,
	PDO::ATTR_PERSISTENT => false,
	PDO::ATTR_EMULATE_PREPARES => true
	);

// Session Settings
$cfg['alloy']['session']['lifetime'] = 28000;

// Locale Settings
$cfg['alloy']['i18n']['charset'] = 'UTF-8';
$cfg['alloy']['i18n']['language'] = 'en_US';
$cfg['alloy']['i18n']['timezone'] = 'America/Chicago';

// Global setup
date_default_timezone_set($cfg['alloy']['i18n']['timezone']);
ini_set("session.gc_maxlifetime", $cfg['alloy']['session']['lifetime']);

return $cfg;