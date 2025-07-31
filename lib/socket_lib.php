<?php

/**
 * Decode WebSocket frame data from client
 */
function decode_data($data)
{
	$opcode = ord($data[0]) & OPCODE_MASK;

	// Only handle text frames
	if ($opcode !== OPCODE_TEXT) {
		return '';
	}

	$payload_length = ord($data[1]) & PAYLOAD_LENGTH_MASK;

	// Determine mask and payload start based on payload size
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

	 // XOR mask to get original message
	$decoded_data = '';
	for ($i = 0; $i < strlen($payload); ++$i) {
		$decoded_data .= $payload[$i] ^ $masks[$i % MASK_LENGTH];
	}

	return $decoded_data;
}

/**
 * Encode a message into a WebSocket frame
 */
function encode_data($data)
{
	$firstByte = FIN_OPCODE_TEXT;
	$length = strlen($data);

	if ($length <= PAYLOAD_MAX_BASIC) {
		$encode_data = chr($firstByte) . chr($length) . $data;
	} elseif ($length <= PAYLOAD_MAX_EXTENDED) {
		$encode_data = chr($firstByte) . chr(PAYLOAD_EXTENDED_16) . pack('n', $length) . $data;
	} else {
		// For very large payloads (64-bit length)
		$encode_data = chr($firstByte) . chr(PAYLOAD_EXTENDED_64) . pack('NN', 0, $length) . $data;
	}

	return $encode_data;
}

/**
 * Perform the WebSocket handshake
 */
function handshake($client)
{
	$headers = [];
	$lines = explode("\r\n", socket_read($client, HANDSHAKE_BUFFER_SIZE));
	
	 // Parse HTTP headers
	foreach ($lines as $line) {
		$line = trim($line);
		if (strpos($line, ':') !== false) {
			[$key, $value] = explode(':', $line, 2);
			$headers[trim($key)] = trim($value);
		}
	}

	 // Sec-WebSocket-Key is required for handshake
	if (!isset($headers['Sec-WebSocket-Key'])) {
		return false;
	}

	$key = $headers['Sec-WebSocket-Key'];
	$accept_key = base64_encode(sha1($key . WEBSOCKET_GUID, true));

	$response = "HTTP/1.1 101 Switching Protocols\r\n";
	$response .= "Upgrade: websocket\r\n";
	$response .= "Connection: Upgrade\r\n";
	$response .= "Sec-WebSocket-Accept: $accept_key\r\n\r\n";

	return handle_socket_write($client, $response);
}

/**
 * Convert JSON string message to array
 */
function get_message_data($data)
{
	return json_decode($data, true);
}

/**
 * Read raw message from socket and decode it
 */
function get_message_raw($client)
{
	$data = socket_read($client, BUFFER_SIZE);
	$decode_data = decode_data($data);

	return $decode_data;
}

/**
 * Send message to a WebSocket client
 */
function send_message($client, $message = [])
{
	$message = json_encode($message);
	$encode_message = encode_data($message);
	
	return handle_socket_write($client, $encode_message);
}

/**
 * Handle safe socket_write with error handling
 */
function handle_socket_write($client, $message)
{
	try {
		$write = socket_write($client, $message, strlen($message));
		if ($write == false) {
			throw new Exception("Socket write failed: " . socket_strerror(socket_last_error($client)));
		}
		return true;
	} catch (Exception $e) {
		echo $e->getMessage();
	}
}
