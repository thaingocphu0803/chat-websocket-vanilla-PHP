<?php
require_once('config/database.php');
require_once('lib/response.php');

//connect database
function connect_db(){
	$dns = get_dsn();
	$username = DB_USER;
	$password = DB_PASSWORD;
	try{
		$conn = new PDO($dns, $username, $password);
 		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $conn;
	}catch(PDOException $e){
		send_response([] ,'pdo-0001', 'ng' ,$e->getMessage());
	}
}

// Create dsn string
function get_dsn()
{
    return DB_ENGINE . ":host=" . DB_HOST . ";dbname=" . DB_NAME;
}

function sql_bind_fetch_one($sql, $param, $pdo)
{
	try {
		$cmd = $pdo->prepare($sql);
		$cmd->setFetchMode(PDO::FETCH_ASSOC);
		foreach ($param as $key => $val) {
			$cmd->bindValue($key, $val);
		}
		$cmd->execute();
		$result = $cmd->fetch();
		
		if($result === false){
			return [];
		} 

		return $result;
	} catch (PDOException $e) {
		send_response([] ,'pdo-0002', 'ng' ,$e->getMessage());
	}
}

