<?php

header("Access-Control-Allow-Origin: http://chattool.local");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

echo json_encode(['code'=>'oke']);