<?php
require_once('config.php');
require_once('helper.php');
require_once('socket_server.php');

$wsServer = new SocketServer(HOST, PORT);
$server = $wsServer->get_socket_server();


$clients = [];

while (true) {
    try {
        $write = [];
        $except = [];
        $read_sockets = array_merge([$server], $clients);

        if (socket_select($read_sockets, $write, $except, TIMEOUT) > 0) {
            foreach ($read_sockets as $socket) {
                if ($socket == $server) {
                    $new_client = socket_accept($server);
                    if ($new_client == false) {
                        throw new Exception('Socket accept client failed:' . socket_strerror(socket_last_error()));
                    }
                    if (handshake($new_client) == false) {
                        throw new Exception('Socket handshake client failed:' . socket_strerror(socket_last_error()));
                    }
                    handle_new_connection($new_client, $clients); // truyền tham chiếu
                } else {
                    handle_message($socket, $clients); // truyền tham chiếu
                }
            }
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        exit;
    }
}

function handle_new_connection($new_client, &$clients)
{
    $client_id = uniqid();
    $clients[$client_id] = $new_client;
    echo "new connection $client_id\n";
}

function handle_message($socket, &$clients)
{
    $data = socket_read($socket, BUFFER_SIZE);
    $decode_data = decode_data($data);
    if ($decode_data === false || $decode_data == '') {
        disconnect_client($socket, $clients);
    } else {
        $message = get_array_message($decode_data);
        send_all_clients($message, $clients);
    }
}

function send_message($client, $message = [])
{
    $message = json_encode($message);
    $encode_message = encode_data($message);
    socket_write($client, $encode_message, strlen($encode_message));
}

function disconnect_client($client, &$clients)
{
    $client_id = array_search($client, $clients, true);
    unset($clients[$client_id]);
    socket_close($client);
    echo "close client ID: $client_id\n";
}

function send_all_clients($message, $clients)
{
    foreach ($clients as $client) {
        send_message($client, $message);
    }
}