<?php

function send_response( array $data = [], string $error_code = '0', string $status = 'ok',  string $message = ''){
	$json = [
		'code' => $error_code,
		'status' =>  $status,
		'message' => $message,
		'data' => $data,
	];

	echo json_encode($json);
	exit;
}