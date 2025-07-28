<?php

require_once('base.php');
require_once('lib/response.php');
require_once('sql/login_sql.php');

session_start();

$payload = get_request_data();

$conn  = connect_db();

// get id and pssw from payload
$userid = $payload['userid'];
$pssw = $payload['pssw'];

try {
	$param = [
		'sql' => get_auth_sql(),
		'param' => [
			':userid' => $userid
		]
	];


	$user = sql_bind_fetch_one($param['sql'], $param['param'], $conn);
	
	// hash pssw to compare with db 
	$hash_input = hash('sha256', $pssw);


	if(empty($user) || !hash_equals($user['pssw'], $hash_input)){
		throw new PDOException('Incorrect user id or password');
	}

	$_SESSION['userid'] = $user['id'];

} catch (PDOException $e) {
	send_response([], 'api-0001', 'ng', $e->getMessage());
}


$data = ['userid' => $_SESSION['userid']];

send_response($data);

