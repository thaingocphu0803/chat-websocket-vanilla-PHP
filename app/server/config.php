<?php

// Server listening IP address
define('WS_HOST', '192.168.0.99');
// Server listening port
define('WS_PORT', 5000);
// Timeout for socket_select (0 = no timeout)
define('TIMEOUT', 0);
// Buffer size when reading data from socket
define('BUFFER_SIZE', 1024);
// Buffer size when reading WebSocket handshake
define('HANDSHAKE_BUFFER_SIZE', 4096);
// Standard WebSocket GUID for Sec-WebSocket-Accept
define('WEBSOCKET_GUID', '258EAFA5-E914-47DA-95CA-C5AB0DC85B11');
// Mask to extract opcode (lowest 4 bits) from the first frame byte
define('OPCODE_MASK', 0x0F);
// Opcode for text frame (text data)
define('OPCODE_TEXT', 0x01);
// First byte for text frame (FIN=1, opcode=1)
define('FIN_OPCODE_TEXT', 0x81);
// Value indicating 16-bit extended payload length
define('PAYLOAD_EXTENDED_16', 126);
// Value indicating 64-bit extended payload length
define('PAYLOAD_EXTENDED_64', 127);
// Maximum payload length for basic frame (<126)
define('PAYLOAD_MAX_BASIC', 125);
// Maximum payload length for 16-bit extended frame
define('PAYLOAD_MAX_EXTENDED', 65535);
// Length of the mask key (per WebSocket standard)
define('MASK_LENGTH', 4);
// Offset for mask key in basic frame
define('MASK_OFFSET_BASIC', 2);
// Offset for mask key in 16-bit extended payload frame
define('MASK_OFFSET_EXT16', 4);
// Offset for mask key in 64-bit extended payload frame
define('MASK_OFFSET_EXT64', 10);
// Offset for payload in basic frame
define('PAYLOAD_OFFSET_BASIC', 6);
// Offset for payload in 16-bit extended payload frame
define('PAYLOAD_OFFSET_EXT16', 8);
// Offset for payload in 64-bit extended payload frame
define('PAYLOAD_OFFSET_EXT64', 14);
// Mask to extract payload length (lowest 7 bits) from the second frame byte
define('PAYLOAD_LENGTH_MASK', 127);

// websoket type connect
define('WS_TYPE_CONNECT', 'connect');

// websocket type chat
define('WS_TYPE_CHAT', 'chat'); 
