<?php
require_once __DIR__ . '/../boostrap.php';

// Get URI path, removing /router.php/ prefix
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/router.php/', '', $uri);

// Get request method (GET or POST)
$method = $_SERVER['REQUEST_METHOD'];

// Define available API routes
$route = [
	'GET' => [
		'auth/getLoginState' => ['AuthController', 'getLoginState'],
		'user/getReceivers' => ['UserController', 'getReceivers'],
		'groupchat/getGroups' => ['GroupChatController', 'getGroups']
	],

	'POST' => [
		'auth/login' => ['AuthController', 'login'],
		'message/getMessage' => ['MessageController', 'getMessage'],
		'groupchat/create' => ['GroupChatController', 'create']
	]
];

// Handle API request
try {
	// Import all controllers
	import_controller('AuthController');
	import_controller('UserController');
	import_controller('MessageController');
	import_controller('GroupChatController');

	// Validate route
	if (!isset($route[$method][$uri])) {
		throw new Exception('API not found.');
	}

	[$class, $action] = $route[$method][$uri];

	// Validate controller class
	if (!class_exists($class)) {
		throw new Exception('Controller not found.');
	}

	 // Instantiate controller and call the method
	$controller =  new $class;
	$controller->$action();

} catch (Exception $e) {
	
	// Return error response with HTTP 500
	http_response_code(500);
	echo json_encode([
		'code' => 500,
		'error' => $e->getMessage()
	]);
}
