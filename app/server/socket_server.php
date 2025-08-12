<?php

require_once __DIR__ . '/../../boostrap.php';

// import MessageController
import_controller('MessageController');

class SocketServer
{
	private $server;
	private $clients = [];
	private $messageController;

	public function __construct($host, $port, $messageController)
	{
		set_time_limit(TIMEOUT);
		$this->messageController = $messageController;
		$this->init_socket($host, $port);
		$this->accept_connection();
	}

	/**
	 * Initialize and bind the socket server
	 */
	private function init_socket($host, $port)
	{
		try {
			// Create TCP socket
			$this->server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($this->server == false) {
				throw new Exception('Socket create failed:' . socket_strerror(socket_last_error()));
			}

			// Allow reuse of address
			$option = socket_set_option($this->server, SOL_SOCKET, SO_REUSEADDR, 1);
			if ($option == false) {
				throw new Exception('Socket set option failed:' . socket_strerror(socket_last_error()));
			}

			// Bind to host:port
			$bind = socket_bind($this->server, $host, $port);
			if ($bind == false) {
				throw new Exception('Socket bind failed:' . socket_strerror(socket_last_error()));
			}

			// Start listening
			$listen = socket_listen($this->server);
			if ($listen == false) {
				throw new Exception('Socket listen failed:' . socket_strerror(socket_last_error()));
			}
		} catch (Exception $e) {
			if($this->server instanceof Socket){
				socket_close($this->server);
			}
			echo $e->getMessage();
			exit;
		}
	}

	/**
	 * Accept new client connections and handle incoming messages
	 */
	private function accept_connection()
	{
		while (true) {
			try {
				$write = [];
				$except = [];
				$read_sockets = array_merge([$this->server], $this->clients);

				// Wait for activity on server or clients
				if (socket_select($read_sockets, $write, $except, TIMEOUT) > 0) {
					foreach ($read_sockets as $socket) {

						// Handle new connection
						if ($socket == $this->server) {
							$new_client = socket_accept($this->server);
							if ($new_client == false) {
								throw new Exception('Socket accept client failed:' . socket_strerror(socket_last_error()));
							}
							if (handshake($new_client) == false) {
								throw new Exception('Socket handshake client failed.');
							}
							$this->handle_new_connection($new_client);

							// Handle incoming message
						} else {
							$this->handle_message($socket);
						}
					}
				}
			} catch (Exception $e) {
				echo $e->getMessage();
				exit;
			}
		}
	}

	/**
	 * Handle a new client connection and store by userid
	 */
	private function handle_new_connection($new_client)
	{
		$raw_message = get_message_raw($new_client);
		$message_data = get_message_data($raw_message);

		if (count($message_data) && $message_data['type'] == WS_TYPE_CONNECT) {
			$client_id = $message_data['data']['userid'];
			$this->clients[$client_id] = $new_client;
			echo "New connection $client_id\n";
		} else {
			echo "Connect failed.\n";
		}
	}

	/**
	 * Handle an incoming message from a client
	 */
	private function handle_message($socket)
	{
		$raw_message = get_message_raw($socket);

		// Client disconnected
		if ($raw_message === false || $raw_message == '') {
			$this->disconnect_client($socket);
		} else {
			$message_data = get_message_data($raw_message);
			$this->send_to_assign_client($message_data);
		}
	}

	/**
	 * Disconnect a client and remove from list
	 */
	private function disconnect_client($socket)
	{
		$client_id = array_search($socket, $this->clients, true);
		if ($client_id) {
			unset($this->clients[$client_id]);
			socket_close($socket);
			echo "close client ID: $client_id\n";
		}
	}

	/**
	 * Send message to the intended receiver client
	 */
	private function send_to_assign_client($message_data)
	{
		if (count($message_data) && $message_data['type'] == WS_TYPE_PCHAT) {
			$this->send_private_message($message_data);

		}else if(count($message_data) && $message_data['type'] == WS_TYPE_GCHAT){
			$this->send_group_message($message_data);
		}
	}


	/**
	 * Handle to send private message
	 */
	private function send_private_message($message_data){
		//Save message to DB first
		$is_savedMessage = $this->messageController->savePrivateMessage($message_data['data']);
		if ($is_savedMessage == false) {
				echo "Failed to send message.\n";
		}

		// Send message to assign receiver sockets
		foreach ($this->clients as $client_id => $client_socket) {
		if ($client_id == $message_data['data']['receiver']) {
			$receiverSocket  = $client_socket;
			send_message($receiverSocket , $message_data);
		}
		}
	}

	/**
	 * Handle to send group message
	 */
	private function send_group_message($message_data){
		echo 111 . "\n";
	}
}

// Start the WebSocket server
$messageController = new MessageController();

new SocketServer(WS_HOST, WS_PORT, $messageController);
