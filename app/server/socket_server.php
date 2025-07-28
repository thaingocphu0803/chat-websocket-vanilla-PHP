<?php

class SocketServer
{
	private $socket_server;

	public function __construct($host, $port)
	{
		$this->init_socket($host, $port);
	}

	// handle to innit socket
	private function init_socket($host, $port)
	{
		try {
			set_time_limit(TIMEOUT);

			$this->socket_server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($this->socket_server == false) {
				throw new Exception('Socket create failed:' . socket_strerror(socket_last_error()));
			}

			$option = socket_set_option($this->socket_server, SOL_SOCKET, SO_REUSEADDR, 1);
			if ($option == false) {
				throw new Exception('Socket set option failed:' . socket_strerror(socket_last_error()));
			}

			$bind = socket_bind($this->socket_server, $host, $port);
			if ($bind == false) {
				throw new Exception('Socket bind failed:' . socket_strerror(socket_last_error()));
			}

			$listen = socket_listen($this->socket_server);
			if ($listen == false) {
				throw new Exception('Socket listen failed:' . socket_strerror(socket_last_error()));
			}
		} catch (Exception $e) {
			socket_close($this->socket_server);
			echo $e->getMessage();
			exit;
		}
	}

	public function get_socket_server()
	{
		return $this->socket_server;
	}
}

