<?php
require_once __DIR__ . '/../../boostrap.php';

/**
 * Class MessageController
 * Handles retrieving and saving chat messages between users
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
	public function getMessage()
	{
		 // Get request payload (JSON)
		$payload = get_request_data();

		 // Prepare param and SQL
		$param  = [
			':receiverid' => $payload['receiverid'],
			':userid' => $payload['userid']
		];
		$sql = <<<SQL
			SELECT
				tb1.sender, tb1.receiver, tb1.message, tb2.name
			FROM 
				messages tb1
			JOIN 
				users tb2
				ON tb2.userid = tb1.sender	
			WHERE
				(sender = :userid AND receiver = :receiverid)
			OR
				(sender = :receiverid AND receiver = :userid)
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
			send_response([], 'mssg-0001', 'ng', 'failed to get receivers');
		}
	}

	/**
     * Save a new message to the database
     */
	public function saveMessage($message_obj)
	{
		try {

		 	// Prepare param and SQL
			$param = [
				':sender' => $message_obj['sender'],
				':receiver' => $message_obj['receiver'],
				':message' => $message_obj['message']
			];
			$sql = <<<SQL
				INSERT INTO messages (sender, receiver, message)
				VALUES(:sender, :receiver, :message)
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
