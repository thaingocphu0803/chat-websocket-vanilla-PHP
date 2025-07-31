<?php

require_once __DIR__ . '/../../boostrap.php';

session_start();

/**
 * Class UserController
 * Handles operations related to users, such as retrieving chat receivers.
 */
class UserController
{
	public function __construct() {}

	/**
     * Get the list of receivers (all users except the current logged-in user)
     */
	public function getReceivers()
	{
		try {
			// Connect to database
			$conn = connect_db();

			// Get current logged-in user ID from session
			$userid = $_SESSION['userid'];

		 	// Prepare param and SQL
			$param = [':userid' => $userid];
			$sql = <<<SQL
				SELECT
					userid, name
				FROM
					users
				WHERE
					userid != :userid
			SQL;

			 // Execute query
			$receivers  = sql_bind_fetchall($sql, $param, $conn);

			// If no receivers found, throw exception
			if (!count($receivers)) {
				throw new PDOException('no record is fetch');
			}

			// Prepare response data
			$data = [
				'receivers' => $receivers
			];

			// Send JSON response to client
			send_response($data);
		} catch (PDOException $e) {
			
			// Return error response if DB query fails
			send_response([], 'user-0001', 'ng', 'failed to get receivers');
		}
	}
}
