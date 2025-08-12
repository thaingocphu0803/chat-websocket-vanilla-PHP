<?php

class GroupChatController {
	
	private $conn;

	public function __construct(){
		// Connect to the database
		$this->conn = connect_db();
	}

	public function create(){
		// Check if session contains a logged-in user
		check_login();
		
		$payload = get_request_data();
		// echo json_encode($payload);
		try {
			
			//start transaction
			transaction_start($this->conn);

			$group = $this->createGroupChat($payload);
			
			if(isset($group['id']) && !is_null($group['id'])){
				$this->createGroupMember($group['id'], $payload);
			}
			// commit transaction
			transaction_commit($this->conn);

			// create response data if create successfully
			$data = [
				'id' => $group['id'],
				'name' => $payload['groupName'],
			];

			send_response($data, '0', 'ok', 'Group created successfully.');
		}catch(PDOException $e){
			// rollback transaction
			transaction_rollback($this->conn);

			send_response([], 'group-0001', 'ng', 'Failed to create group.');
		}
	}

	/**
     * Get the list of group
     */
	public function getGroups()
	{
		// Check if session contains a logged-in user
		check_login();
		
		try {

		 	// Prepare param and SQL
			$param = [':member_id' => $_SESSION['userid']];
			$sql = <<<SQL
				SELECT
					tb1.id , tb1.name
				FROM
					group_chat tb1
				JOIN
					group_member tb2
				ON
					tb1.id = tb2.group_id
				WHERE
					tb2.member_id = :member_id
			SQL;

			 // Execute query
			$groups  = sql_bind_fetchall($sql, $param, $this->conn);

			// If no receivers found, throw exception
			if (!count($groups)) {
				throw new PDOException('no record is fetch');
			}

			// Prepare response data
			$data = [
				'groups' => $groups
			];

			// Send JSON response to client
			send_response($data);
		} catch (PDOException $e) {
			
			// Return error response if DB query fails
			send_response([], 'group-0002', 'ng', 'Failed to get groups');
		}
	}

	private function createGroupChat($payload){
		$sql = <<<SQL
				INSERT INTO group_chat (name, created_by)
				VALUES(:name, :created_by)
			SQL;

		$param = [
			':name' => $payload['groupName'],
			':created_by' => $_SESSION['userid']
		];

		try{
			sql_bind_exec($sql, $param, $this->conn);

			$id = get_insert_id($this->conn);

			return ['id'=> $id];
		}catch(PDOException $e){
			throw new PDO($e->getMessage());
		}
	}

	private function createGroupMember($group_id, $payload){
		$sql = <<<SQL
				INSERT INTO group_member (group_id, member_id)
				VALUES(:group_id, :member_id)
			SQL;

		// handle member ID array
		$payload['memberIds'][] = $_SESSION['userid'];

		$param_map = array_map(function($member_id) use ($group_id){
			return [
				':group_id' => $group_id,
				':member_id' => $member_id
			];
		}, $payload['memberIds']);

		try{
			sql_bind_exec_batch($sql, $param_map, $this->conn);
		}catch(PDOException $e){
			throw new PDO($e->getMessage());
		}
	}
}