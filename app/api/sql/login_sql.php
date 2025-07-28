<?php

function get_auth_sql(){
	return <<<SQL
		SELECT 
			id, pssw
		FROM
			users
		WHERE
			userid = :userid
		LIMIT 1
	SQL;
}