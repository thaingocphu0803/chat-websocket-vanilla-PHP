<?php
require_once __DIR__ . '/../../boostrap.php';

/**
 * Class MessageController
 */
class MessageController
{
	private $conn;

	public function __construct()
	{
		$this->conn =  connect_db();
	}

	/**
	 * Fetch all messages between two users
	 */
	public function getPrivateMessage()
	{
		// Get request payload (JSON)
		$payload = get_request_data();

		// Prepare param and SQL
		$param  = [
			':partnerid' => $payload['partnerid'],
			':userid' => $payload['userid']
		];
		$sql = <<<SQL
			SELECT
				tb1.sender, tb1.partner, tb1.message, tb2.name
			FROM 
				messages tb1
			JOIN 
				users tb2
				ON tb2.userid = tb1.sender	
			WHERE
				(sender = :userid AND partner = :partnerid)
			OR
				(sender = :partnerid AND partner = :userid)
			ORDER BY 
				created_at
		SQL;

		try {
			// Fetch all messages from database
			$messages = sql_bind_fetchall($sql, $param, $this->conn);

			// Prepare response data
			$data = [
				'messages' => $messages
			];

			// Send JSON response to client
			send_response($data);
		} catch (PDOException $e) {

			// DB error or query failed
			send_response([], 'mssg-0001', 'ng', 'failed to get partner message');
		}
	}

	/**
	 * Save a new private message to the database
	 */
	public function savePrivateMessage($message_obj)
	{
		try {
			// Prepare param and SQL
			$param = [
				':sender' => $message_obj['sender'],
				':partner' => $message_obj['receiver'],
				':message' => $message_obj['message']
			];
			$sql = <<<SQL
				INSERT INTO messages (sender, partner, message)
				VALUES(:sender, :partner, :message)
			SQL;

			// Execute insert
			sql_bind_exec($sql, $param, $this->conn);

			return true;
		} catch (PDOException $e) {

			// Return false if DB error occurs
			return false;
		}
	}

	/**
	 * Fetch all messages on group chat
	 */
	public function getGroupMessage()
	{
		// Get request payload (JSON)
		$payload = get_request_data();

		// Prepare param and SQL
		$param  = [
			':group_uid' => $payload['groupUid'],
		];

		$sql = <<<SQL
			SELECT
				tb1.sender, tb1.message, tb2.name
			FROM 
				messages tb1
			JOIN 
				users tb2
				ON tb2.userid = tb1.sender	
			WHERE
				group_uid = :group_uid
			ORDER BY 
				created_at
		SQL;

		try {
			// Fetch all messages from database
			$messages = sql_bind_fetchall($sql, $param, $this->conn);

			// Prepare response data
			$data = [
				'messages' => $messages
			];

			// Send JSON response to client
			send_response($data);
		} catch (PDOException $e) {

			// DB error or query failed
			send_response([], 'mssg-0002', 'ng', 'failed to get group message');
		}
	}

	/**
	 * Save a new group message to the database
	 */
	public function saveGroupMessage($message_obj)
	{
		try {

			// Prepare param and SQL
			$param = [
				':sender' => $message_obj['sender'],
				':group_uid' => $message_obj['receiver'],
				':message' => $message_obj['message']
			];
			$sql = <<<SQL
				INSERT INTO messages (sender, group_uid, message)
				VALUES(:sender, :group_uid, :message)
			SQL;

			// Execute insert
			sql_bind_exec($sql, $param, $this->conn);

			return true;
		} catch (PDOException $e) {

			// Return false if DB error occurs
			return false;
		}
	}
}
