<?php

/**
 * Send a standardized JSON response and terminate the script
 */
function send_response(array $data = [], string $error_code = '0', string $status = 'ok',  string $message = '')
{
	$json = [
		'code' => $error_code,
		'status' =>  $status,
		'message' => $message,
		'data' => $data,
	];

	echo json_encode($json);
	exit;
}

/**
 * Get and validate incoming JSON request data
 */
function get_request_data()
{
	global $request;

	// Chain request validation and JSON parsing
	$data =  $request->is_post()
		->is_contentType('application/json')
		->receive_json()
		->accept();

	// Handle error in request
	if ($data['error_code'] != 0) {
		send_response([], $data['error_code'], 'ng', 'Failed to get request data.');
	}

	return $data['result'];
}

/**
 * Check if user is logged in via session.
 */
function check_login()
{
	if (!isset($_SESSION['userid']) || is_null($_SESSION['userid'])) {
		send_response([], 'api-0001', 'ng', 'user is logged out.');
	}
}
