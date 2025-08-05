<?php

// WebSocket server listening address and port
define('WS_HOST', '192.168.0.99');
define('WS_PORT', 5000);

// Socket select timeout (0 = no timeout)
define('TIMEOUT', 0);

// Buffer sizes
define('BUFFER_SIZE', 1024);            // For normal socket read
define('HANDSHAKE_BUFFER_SIZE', 4096);  // For WebSocket handshake

// Standard WebSocket GUID for Sec-WebSocket-Accept
define('WEBSOCKET_GUID', '258EAFA5-E914-47DA-95CA-C5AB0DC85B11');

// Frame opcodes and masks
define('OPCODE_MASK', 0x0F);        // Mask for extracting opcode
define('OPCODE_TEXT', 0x01);        // Text frame opcode
define('FIN_OPCODE_TEXT', 0x81);    // FIN=1 + text frame

// Payload length indicators
define('PAYLOAD_EXTENDED_16', 126); // 16-bit extended payload
define('PAYLOAD_EXTENDED_64', 127); // 64-bit extended payload

// Maximum payload lengths
define('PAYLOAD_MAX_BASIC', 125);      // Basic frame (<126)
define('PAYLOAD_MAX_EXTENDED', 65535); // 16-bit extended frame

// WebSocket mask key info
define('MASK_LENGTH', 4);              // Always 4 bytes
define('MASK_OFFSET_BASIC', 2);        // Mask start for basic frame
define('MASK_OFFSET_EXT16', 4);        // Mask start for 16-bit extended frame
define('MASK_OFFSET_EXT64', 10);       // Mask start for 64-bit extended frame

// Payload offsets
define('PAYLOAD_OFFSET_BASIC', 6);     // Payload start for basic frame
define('PAYLOAD_OFFSET_EXT16', 8);     // Payload start for 16-bit frame
define('PAYLOAD_OFFSET_EXT64', 14);    // Payload start for 64-bit frame

// Mask to extract payload length from second frame byte
define('PAYLOAD_LENGTH_MASK', 127);

// WebSocket message types
define('WS_TYPE_CONNECT', 'connect');  // Connection event
define('WS_TYPE_CHAT', 'chat');        // Chat message
