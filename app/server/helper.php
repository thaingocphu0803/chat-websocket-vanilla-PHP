<?php

// Decodes data received from the WebSocket client
function decode_data($data)
{
	$opcode = ord($data[0]) & OPCODE_MASK;

	if ($opcode !== OPCODE_TEXT) {
		return '';
	}

	$payload_length = ord($data[1]) & PAYLOAD_LENGTH_MASK;

	if ($payload_length === PAYLOAD_EXTENDED_16) {
		$masks = substr($data, MASK_OFFSET_EXT16, MASK_LENGTH);
		$payload = substr($data, PAYLOAD_OFFSET_EXT16);
	} elseif ($payload_length === PAYLOAD_EXTENDED_64) {
		$masks = substr($data, MASK_OFFSET_EXT64, MASK_LENGTH);
		$payload = substr($data, PAYLOAD_OFFSET_EXT64);
	} else {
		$masks = substr($data, MASK_OFFSET_BASIC, MASK_LENGTH);
		$payload = substr($data, PAYLOAD_OFFSET_BASIC);
	}

	$decoded_data = '';
	for ($i = 0; $i < strlen($payload); ++$i) {
		$decoded_data .= $payload[$i] ^ $masks[$i % MASK_LENGTH];
	}

	return $decoded_data;
}

// Encodes data to send to the WebSocket client
function encode_data($data)
{
	$firstByte = FIN_OPCODE_TEXT;
	$length = strlen($data);

	if ($length <= PAYLOAD_MAX_BASIC) {
		$encode_data = chr($firstByte) . chr($length) . $data;
	} elseif ($length <= PAYLOAD_MAX_EXTENDED) {
		$encode_data = chr($firstByte) . chr(PAYLOAD_EXTENDED_16) . pack('n', $length) . $data;
	} else {
		$encode_data = chr($firstByte) . chr(PAYLOAD_EXTENDED_64) . pack('NN', 0, $length) . $data;
	}

	return $encode_data;
}

// Performs the handshake with the client when establishing a WebSocket connection
function handshake($client)
{
	$headers = [];
	$lines = explode("\r\n", socket_read($client, HANDSHAKE_BUFFER_SIZE));
	foreach ($lines as $line) {
		$line = trim($line);
		if (strpos($line, ':') !== false) {
			[$key, $value] = explode(':', $line, 2);
			$headers[trim($key)] = trim($value);
		}
	}

	if(!isset($headers['Sec-WebSocket-Key'])){
		return false;
	}

	$key = $headers['Sec-WebSocket-Key'];
	$accept_key = base64_encode(sha1($key . WEBSOCKET_GUID , true));

	$response = "HTTP/1.1 101 Switching Protocols\r\n";
	$response .= "Upgrade: websocket\r\n";
	$response .= "Connection: Upgrade\r\n";
	$response .= "Sec-WebSocket-Accept: $accept_key\r\n\r\n";

	try{
		socket_write($client, $response, strlen($response));
		return true;
	}catch(Exception $e){
		
	}


}


function get_array_message($data)
{
	return json_decode($data, true);
}
