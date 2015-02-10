<?php

// Development environment
if (!ini_get('display_errors')) 
{
    ini_set('display_errors', '1');
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    error_reporting(E_ALL);
}

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Set localtime zone
date_default_timezone_set("America/Bogota");

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
