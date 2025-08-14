<?php
/**
 * Class GroupController
 */
class GroupController {
	
	private $conn;

	public function __construct(){
		// Connect to the database
		$this->conn = connect_db();
	}

	/**
	 * Create a new group chat with members
	 */
	public function create(){
		// Check if session contains a logged-in user
		check_login();
		
		// Get request data from client
		$payload = get_request_data();

		try {
			// Start DB transaction
			transaction_start($this->conn);
			
			// Create group chat record
			$group = $this->createGroupChat($payload);
			
			// Add members if group created successfully
			if(isset($group['group_uid']) && !is_null($group['group_uid'])){
				$this->createGroupMember($group['group_uid'], $payload);
			}
			// commit transaction
			transaction_commit($this->conn);

			// Send success response
			send_response([], '0', 'ok', 'Group created successfully.');
		}catch(PDOException $e){
			// rollback transaction
			transaction_rollback($this->conn);

			// Send error response
			send_response([], 'group-0001', 'ng', 'Failed to create group.');
		}
	}

	/**
     * Get the list of groups that the current user belongs to
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
					tb1.group_uid as id , tb1.name
				FROM
					group_chat tb1
				JOIN
					group_member tb2
				ON
					tb1.group_uid = tb2.group_uid
				WHERE
					tb2.member_id = :member_id
			SQL;

			 // Execute query
			$groups  = sql_bind_fetchall($sql, $param, $this->conn);

			// If no group found, throw exception
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

	/**
	 * Get member IDs in a group except the given member
	 */
	public function getMemberByGroupUid($group_uid, $member_id){
		
		// Prepare param and SQL
		$param = [
			':group_uid' => $group_uid,
			':member_id' => $member_id
		];
		$sql = <<< SQL
			SELECT 
				member_id
			FROM
				group_member
			WHERE
				group_uid = :group_uid
			AND
				member_id != :member_id
		SQL;

		try{
			// Execute query
			$members = sql_bind_fetchall($sql, $param, $this->conn);

			// If no member found, throw exception
			if(empty($members)){
				throw new PDOException("The Group is no member.");
			}

			// create new array contained member id and return
			$memberIds = array_column($members, 'member_id');
			return $memberIds;
		}catch(PDOException $e){
			// return false if having any exception
			return false;
		}
	}
	
	/**
	 * Create a new group chat record in DB
	 */
	private function createGroupChat($payload){

		// Generate unique group UID
		$group_uid = $this->createGroupUid($payload['groupName'], $_SESSION['userid']);

		// Prepare param and SQL
		$sql = <<<SQL
				INSERT INTO group_chat (group_uid, name, created_by)
				VALUES(:group_uid, :name, :created_by)
			SQL;
		$param = [
			':group_uid' => $group_uid,
			':name' => $payload['groupName'],
			':created_by' => $_SESSION['userid']
		];

		try{
			// Execute query and return group UID
			sql_bind_exec($sql, $param, $this->conn);
			return ['group_uid'=> $group_uid];
		}catch(PDOException $e){
			// throw PDOException if having any error
			throw new PDOException($e->getMessage());
		}
	}

	/**
	 * Insert multiple group members into DB
	 */
	private function createGroupMember($group_uid, $payload){
		
		// Prepare param and SQL
		$sql = <<<SQL
				INSERT INTO group_member (group_uid, member_id)
				VALUES(:group_uid, :member_id)
			SQL;

		// Add creator to member list
		$payload['memberIds'][] = $_SESSION['userid'];

		// Map each member to SQL parameters
		$param_map = array_map(function($member_id) use ($group_uid){
			return [
				':group_uid' => $group_uid,
				':member_id' => $member_id
			];
		}, $payload['memberIds']);

		try{
			// Execute query
			sql_bind_exec_batch($sql, $param_map, $this->conn);
		}catch(PDOException $e){
			// throw PDOException if having any error
			throw new PDOException($e->getMessage());
		}
	}

	/**
	 * Generate a unique group UID
	 */
	private function createGroupUid($groupName, $creator_id, $length = 12, $bytes = 8){
		// Generate a salt using current microtime + random bytes (to ensure high uniqueness)
		$salt = microtime(true). bin2hex(random_bytes($bytes));
		
		// Create a SHA-256 hash from groupName + creator_id + salt (strong uniqueness & randomness)
		$hash =  hash('sha256', $groupName. $creator_id . $salt);
		
		// Take the first N characters from hash as the unique ID
		$id = substr($hash, 0, $length);
		
		// Return the final group UID in the format: group_<hash_part>
		return "group_$id";
	}
}