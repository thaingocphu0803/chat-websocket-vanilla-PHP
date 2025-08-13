<?php

require_once __DIR__ . '/../../boostrap.php';

session_start();

/**
 * Class UserController
 */
class UserController
{
	private $conn;

	public function __construct() {
		// Connect to database
		$this->conn = connect_db();
	}

	/**
     * Get the list of partners (all users except the current logged-in user)
     */
	public function getPartners()
	{
		// Check if session contains a logged-in user
		check_login();
		
		try {

		 	// Prepare param and SQL
			$param = [':userid' => $_SESSION['userid']];
			$sql = <<<SQL
				SELECT
					userid as id, name
				FROM
					users
				WHERE
					userid != :userid
			SQL;

			 // Execute query
			$partners  = sql_bind_fetchall($sql, $param, $this->conn);

			// If no partners found, throw exception
			if (!count($partners)) {
				throw new PDOException('no record is fetch');
			}

			// Prepare response data
			$data = [
				'partners' => $partners
			];

			// Send JSON response to client
			send_response($data);
		} catch (PDOException $e) {
			
			// Return error response if DB query fails
			send_response([], 'user-0001', 'ng', 'failed to get partners');
		}
	}
}
