<?php
require_once('lib/request.php');

require_once('lib/connect.php');

function get_request_data()
{
	global $request;
	
	$data =  $request->is_post()
			->is_contentType('application/json')
			->receive_json()
			->accept();
	
	if($data['error_code'] != 0){
		send_response([] ,$data['error_code'], 'ng' ,'Failed to get request data.');
	}

	return $data['result'];

}
