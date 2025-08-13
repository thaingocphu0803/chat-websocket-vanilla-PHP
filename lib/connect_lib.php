<?php

/**
 * Connect to the database using PDO
 */
function connect_db()
{
	$dns = get_dsn();
	$username = DB_USER;
	$password = DB_PASSWORD;
	try {
		$conn = new PDO($dns, $username, $password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $conn;
	} catch (PDOException $e) {
		// Return standardized error response if connection fails
		send_response([], 'pdo-0001', 'ng', 'Failed to connect db.');
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
function sql_bind_fetchall($query, $param, $pdo)
{
	try {
		$cmd = $pdo->prepare($query);

		// Bind parameters
		foreach ($param as $key => $val) {
			$cmd->bindValue($key, $val);
		}

		$cmd->setFetchMode(PDO::FETCH_ASSOC);
		$cmd->execute();
		$result = $cmd->fetchAll();

		return $result;
	} catch (PDOException $e) {
		throw new PDOException('Failed to fetch the record.');
	}
}

/**
 * Execute SQL with bound params (INSERT/UPDATE/DELETE)
 */
function sql_bind_exec($query, $param, $pdo)
{
	try {
		$cmd = $pdo->prepare($query);

		foreach ($param as $key => $val) {
			$cmd->bindValue($key, $val);
		}

		$cmd->execute();

		return $cmd;
	} catch (PDOException $e) {
		throw new PDOException('Failed to execute sql');
	}
}

/**
 * Roll back the current database transaction
 */
function transaction_rollback(PDO $conn)
{
	if (!$conn->rollBack()) {
		send_response([], 'pdo-0002', 'ng', 'Failed to rollback db.');
	}
	return true;
}

/**
 * Start a database transaction
 */
function transaction_start(PDO $conn)
{
	if (!$conn->beginTransaction()) {
		throw new PDOException("faied to start transaction.");
	}
	return true;
}

/**
 * Commit the current database transaction
 */
function transaction_commit(PDO $conn)
{
	if (!$conn->commit()) {
		throw new PDOException("faied to commit transaction.");
	}
	return true;
}

/**
 * Retrieve the last inserted ID from a PDO connection.
 */
function get_last_insert_id(PDO $conn)
{
	$id = $conn->lastInsertId();
	if (empty($id)) {
		throw new PDO("No last insert ID available.");
	}
	return $id;
}

/**
 * Batch execute SQL with different bind parameters.
 */
function sql_bind_exec_batch($sql, $param_map, $pdo)
{
	try {
		if (empty($param_map)) {
			throw new PDOException('Param map is empty.');
		};

		$cmd = $pdo->prepare($sql);

		foreach ($param_map as $param) {
			if (empty($param)) {
				throw new PDOException('One of the parameter sets is empty.');
			}

			foreach ($param as $key => $val) {
				$cmd->bindValue($key, $val);
			}

			$cmd->execute();
		}
	} catch (PDOException $e) {
		throw new PDOException('Failed to execute batch SQL: ' . $e->getMessage());
	}
}
