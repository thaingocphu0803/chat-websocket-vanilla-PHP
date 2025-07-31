<?php
// Define the project base path as the current directory
define('BASE_PATH', realpath(__DIR__));

/**
 * Import a controller file by its name
 */
function import_controller($controllerName){
	
	// Build the file path to the controller
	$filePath = BASE_PATH. '/app/api/'. $controllerName. '.php';
	
	 // Check if the file exists before requiring it
	if(!file_exists($filePath)){
		throw new Exception('Can not found file.');
	}
	require_once $filePath;	
}

// Load library for API handling
require_once BASE_PATH. '/lib/api_lib.php';

// Load library for database connection
require_once BASE_PATH. '/lib/connect_lib.php';

// Load library for HTTP request handling
require_once BASE_PATH. '/lib/request_lib.php';

// Load library for WebSocket server
require_once BASE_PATH. '/lib/socket_lib.php';

// Load configuration for WebSocket server
require_once BASE_PATH. '/config/socket_config.php';

// Load configuration for database
require_once BASE_PATH. '/config/database_config.php';
