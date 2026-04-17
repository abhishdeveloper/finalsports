<?php
/**
 * Main Entry Point
 */

// Define directory constants
define('ROOT_DIR', dirname(__DIR__));
define('APP_DIR', ROOT_DIR . '/app');
define('CORE_DIR', ROOT_DIR . '/core');
define('CONFIG_DIR', ROOT_DIR . '/config');

// Require the core application
require_once CORE_DIR . '/App.php';
require_once CORE_DIR . '/bootstrap.php';

// Start secure session
Session::start();

// Initialize application
$app = new App();
$app->run();
