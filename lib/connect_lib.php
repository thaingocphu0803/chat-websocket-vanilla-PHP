<?php

/**
 * Connect to the database using PDO
 */
function connect_db(){
	$dns = get_dsn();
	$username = DB_USER;
	$password = DB_PASSWORD;
	try{
		$conn = new PDO($dns, $username, $password);
 		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $conn;
	}catch(PDOException $e){
		// Return standardized error response if connection fails
		throw new PDOException('Failed to connect db.');
	}
}

/**
 * Build the DSN string for PDO
 */
function get_dsn()
{
    return DB_ENGINE . ":host=" . DB_HOST . ";dbname=" . DB_NAME;
}


/**
 * Execute SQL with bound params and fetch a single row (assoc array)
 */
function sql_bind_fetch_one($sql, $param, $pdo)
{
	try {
		$cmd = $pdo->prepare($sql);
		$cmd->setFetchMode(PDO::FETCH_ASSOC);
		// Bind parameters
		foreach ($param as $key => $val) {
			$cmd->bindValue($key, $val);
		}
		$cmd->execute();
		$result = $cmd->fetch();
		
 		// Return empty array if no record
        return $result === false ? [] : $result;

	} catch (PDOException $e) {
		throw new PDOException('Failed to fetch the record.');
	}
}

/**
 * Execute SQL with bound params and fetch all rows (assoc array)
 */
function sql_bind_fetchall($query, $param, $pdo) {
	try {
		$cmd = $pdo->prepare($query);
		
		// Bind parameters
		foreach ($param as $key => $val) {
			$cmd->bindValue($key, $val);
		}

		$cmd->setFetchMode(PDO::FETCH_ASSOC);
		$cmd->execute();
		$result = $cmd->fetchAll();
		
		return $result ;

	} catch(PDOException $e) {
		throw new PDOException('Failed to fetch the record.');
	}
}

/**
 * Execute SQL with bound params (INSERT/UPDATE/DELETE)
 */
function sql_bind_exec($query, $param, $pdo) {
	try {
		$cmd = $pdo->prepare($query);

		foreach ($param as $key => $val) {
			$cmd->bindValue($key, $val);
		}

		$cmd->execute();

		return $cmd;
	} catch(PDOException $e) {
		throw new PDOException('Failed to execute sql');
	}
}
