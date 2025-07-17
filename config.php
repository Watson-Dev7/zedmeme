<?php
// Define application environment
define('ENVIRONMENT', 'development');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'zedmemes');
define('DB_USER', 'root');
define('DB_PASS', 'Nawa911?');

// Error reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
