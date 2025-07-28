<?php

// Server listening IP address
const HOST = '192.168.0.99';
// Server listening port
const PORT = 8000;
// Timeout for socket_select (0 = no timeout)
const TIMEOUT = 0;
// Buffer size when reading data from socket
const BUFFER_SIZE = 1024;
// Buffer size when reading WebSocket handshake
const HANDSHAKE_BUFFER_SIZE = 4096;
// Standard WebSocket GUID for Sec-WebSocket-Accept
const WEBSOCKET_GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
// Mask to extract opcode (lowest 4 bits) from the first frame byte
const OPCODE_MASK = 0x0F;
// Opcode for text frame (text data)
const OPCODE_TEXT = 0x01;
// First byte for text frame (FIN=1, opcode=1)
const FIN_OPCODE_TEXT = 0x81;
// Value indicating 16-bit extended payload length
const PAYLOAD_EXTENDED_16 = 126;
// Value indicating 64-bit extended payload length
const PAYLOAD_EXTENDED_64 = 127;
// Maximum payload length for basic frame (<126)
const PAYLOAD_MAX_BASIC = 125;
// Maximum payload length for 16-bit extended frame
const PAYLOAD_MAX_EXTENDED = 65535;
// Length of the mask key (per WebSocket standard)
const MASK_LENGTH = 4;
// Offset for mask key in basic frame
const MASK_OFFSET_BASIC = 2;
// Offset for mask key in 16-bit extended payload frame
const MASK_OFFSET_EXT16 = 4;
// Offset for mask key in 64-bit extended payload frame
const MASK_OFFSET_EXT64 = 10;
// Offset for payload in basic frame
const PAYLOAD_OFFSET_BASIC = 6;
// Offset for payload in 16-bit extended payload frame
const PAYLOAD_OFFSET_EXT16 = 8;
// Offset for payload in 64-bit extended payload frame
const PAYLOAD_OFFSET_EXT64 = 14;
// Mask to extract payload length (lowest 7 bits) from the second frame byte
const PAYLOAD_LENGTH_MASK = 127;
