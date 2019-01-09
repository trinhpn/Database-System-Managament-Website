<?php

	function get_connection() {
		$userid   = '_tpca445'; //Change this to yours
		$password = 'database445'; //Change this to yours
		$host     = 'cssgate.insttech.washington.edu';
		$dbname   = '_tpca445'; //Change this to yours
		
		$dsn = 'mysql:host='.$host. ';dbname='.$dbname;
		
		try {
		    $db = new PDO($dsn, $userid, $password);
		}
		catch(PDOException $e) {
			echo "Error connecting to database";
	    }
	    return $db;
	}
?>