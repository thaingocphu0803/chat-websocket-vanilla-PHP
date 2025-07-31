<?php
require_once __DIR__ . '/../../boostrap.php';

session_start();

/**
 * Class AuthController
 * Handles user authentication (login & session check)
 */
class AuthController
{


	public function __construct() {}

	/**
     * Handle user login request
     */
	public function login()
	{
		try {
			 // Connect to the database
			$conn = connect_db();

			// Get request payload (JSON) and extract userid/password
			$payload = get_request_data();
			$userid = $payload['userid'];
			$pssw = $payload['pssw'];

		 	// Prepare param and SQL
			$param = [
				':userid' => $userid
			];
			$sql = <<<SQL
				SELECT 
					userid, pssw, name
				FROM
					users
				WHERE
					userid = :userid
				LIMIT 1
			SQL;

  			// Execute SQL and fetch user info
			$user = sql_bind_fetch_one($sql, $param, $conn);

			// Hash the input password to compare with stored hash
			$hash_input = hash('sha256', $pssw);

 			// Validate user & password
			if (empty($user) || !hash_equals($user['pssw'], $hash_input)) {
				send_response([], 'auth-0001', 'ng', 'Incorrect user id or password');
			}

			// Save user info to session after successful login
			$_SESSION['userid'] = $user['userid'];
			$_SESSION['name'] = $user['name'];

			// Prepare response data
			$data = [
				'userid' => $_SESSION['userid'],
				'name' => $_SESSION['name']
			];

			// Send JSON response to client
			send_response($data);
		} catch (PDOException $e) {

			 // Database or query error
			send_response([], 'auth-0002', 'ng', 'Login failed.');
		}
	}

	/**
     * Check current login state
     */
	public function getLoginState()
	{
		try {
			// Check if session contains a logged-in user
			if (!isset($_SESSION['userid']) || is_null($_SESSION['userid'])) {
				throw new Exception('User is logged out.');
			}

			// Prepare user info from session
			$data = [
				'userid' => $_SESSION['userid'],
				'name' => $_SESSION['name']

			];

			// Send JSON response
			send_response($data);
		} catch (Exception $e) {

			// User not logged in
			send_response([], 'auth-0003', 'ng', $e->getMessage());
		}
	}
}
