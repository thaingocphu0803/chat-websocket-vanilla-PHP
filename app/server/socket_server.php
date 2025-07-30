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
		$this->messageController = $messageController;
		$this->init_socket($host, $port);
		$this->accept_connection();
	}

	// handle to innit socket
	private function init_socket($host, $port)
	{
		try {
			set_time_limit(TIMEOUT);

			$this->server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($this->server == false) {
				throw new Exception('Socket create failed:' . socket_strerror(socket_last_error()));
			}

			$option = socket_set_option($this->server, SOL_SOCKET, SO_REUSEADDR, 1);
			if ($option == false) {
				throw new Exception('Socket set option failed:' . socket_strerror(socket_last_error()));
			}

			$bind = socket_bind($this->server, $host, $port);
			if ($bind == false) {
				throw new Exception('Socket bind failed:' . socket_strerror(socket_last_error()));
			}

			$listen = socket_listen($this->server);
			if ($listen == false) {
				throw new Exception('Socket listen failed:' . socket_strerror(socket_last_error()));
			}
		} catch (Exception $e) {
			socket_close($this->server);
			echo $e->getMessage();
			exit;
		}
	}

	private function accept_connection()
	{
		while (true) {
			try {
				$write = [];
				$except = [];
				$read_clients = array_merge([$this->server], $this->clients);

				if (socket_select($read_clients, $write, $except, TIMEOUT) > 0) {
					foreach ($read_clients as $client) {
						if ($client == $this->server) {
							$new_client = socket_accept($this->server);
							if ($new_client == false) {
								throw new Exception('Socket accept client failed:' . socket_strerror(socket_last_error()));
							}
							if (handshake($new_client) == false) {
								throw new Exception('Socket handshake client failed:' . socket_strerror(socket_last_error()));
							}
							$this->handle_new_connection($new_client);
						} else {
							$this->handle_message($client);
						}
					}
				}
			} catch (Exception $e) {
				echo $e->getMessage();
				exit;
			}
		}
	}

	private function handle_new_connection($new_client)
	{
		$raw_message = get_message_raw($new_client);
		$message_arr = get_message_arr($raw_message);

		if (count($message_arr) && $message_arr['type'] == WS_TYPE_CONNECT) {
			$client_id = $message_arr['data']['userid'];
			$this->clients[$client_id] = $new_client;
			echo "New connection $client_id\n";
		} else {
			echo "Connect failed.\n";
		}
	}

	private function handle_message($client)
	{
		$raw_message = get_message_raw($client);

		if ($raw_message === false || $raw_message == '') {
			$this->disconnect_client($client);
		} else {
			$message_arr = get_message_arr($raw_message);
			$this->send_to_assign_client($message_arr);
		}
	}

	function disconnect_client($client)
	{
		$client_id = array_search($client, $this->clients, true);
		if ($client_id) {
			unset($this->clients[$client_id]);
			socket_close($client);
			echo "close client ID: $client_id\n";
		}
	}

	function send_to_assign_client($message_arr)
	{
		if (count($message_arr) && $message_arr['type'] == WS_TYPE_CHAT) {
			foreach ($this->clients as $key => $val) {
				if ($key == $message_arr['data']['receiver']) {
					$client = $val;

					// sending message to assign client if saving message to db successfully
					$is_savedMessage = $this->messageController->saveMessage($message_arr['data']);
					if ($is_savedMessage) {
						send_message($client, $message_arr);
					}
				}
			}
		} else {
			echo "Failed to send message.\n";
		}
	}
}

$messageController = new MessageController();

new SocketServer(WS_HOST, WS_PORT, $messageController);
