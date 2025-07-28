<?php
require_once('lib/response.php');


session_start();


if ($_SERVER['REQUEST_METHOD'] !== "GET") {
	send_response([], 'r-0001', 'Incorrect request method.');
} else {
	$data = [
		'userid' => $_SESSION['userid'] ?? 0
	];

	send_response($data);
}
