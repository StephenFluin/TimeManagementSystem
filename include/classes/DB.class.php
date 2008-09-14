<?php	

class DB {
	var $database;
	var $databaseName;
	var $results;
	var $table;
	
	function DB($databaseName = "tms") {
		if(isset($GLOBALS["dbConnections"]) && isset($GLOBALS["dbConnections"][$databaseName])) {
			//print "Using a found connection for $databaseName.<br/>\n";
			$this->database = $GLOBALS["dbConnections"][$databaseName]->database;
			$this->databaseName = $GLOBALS["dbConnections"][$databaseName]->databaseName;
			
		} else {
			//print "Making a new connection for $databaseName.<br/>\n";
			$this->database = mysql_connect($GLOBALS["db_host"], $GLOBALS["db_username"], $GLOBALS["db_password"], true);
			if(!$this->database) {
				showError("Couldn't connect to the database.  " . mysql_error());
			} else {
				//if($_GET["action"] == "debug") {
					//print "Created a new DB connection to " . $databaseName . ".";
				//}
			}
			mysql_select_db( $databaseName , $this->database );
			$this->databaseName = $databaseName;
			$GLOBALS["dbConnections"][$databaseName] = $this;
			echo mysql_error();
		}
		
	}
	
	
	function newInstance() {
		return new DB($this->database);
	}
	// takes in a query and runs it
	function query( $query ) {
		if ($_GET["action"] == "debug") $time_start = microtime();

		$this->lastQuery = $query;
		$this->results = mysql_query($query, $this->database);

		if ($_GET["action"] == "debug") $time = microtime() - $time_start;
		
		if(!$this->results) {
			if($_GET["action"] == "debug") {
				print "Failed query:" . $time . "\t" . $query . "<br/>\n";
				print "Problem: " . mysql_error();
			}
			showError("Server Error: $query , " . mysql_error() . " on " . $this->databaseName . "(" . mysql_thread_id($this->database) . ").");
			exit;
		} else {
			if($_SESSION["userid"] == "90") {
				//print "SQL Command successfull on " . $this->databaseName . "(" . mysql_thread_id($this->database) . ").";
			}
		}
		if($_GET["action"] == "debug") {
			$_SESSION["queryList"][$query] = $time . "\t" . $query;
			print $time . "\t" . $query . "<br/>\n";
		}

	}
	function getLastQuery() {
                return $this->lastQuery;
        }
        function escape($string) {
                return mysql_real_escape_string($string);
        }
        function size() {
                return mysql_num_rows($this->results);
        }
        function fetchrow() {
                return mysql_fetch_array( $this->results , MYSQL_NUM );
        }
        function getScalar() {
                list($result) = mysql_fetch_array($this->results, MYSQL_NUM);
                return $result;
        }
        function getInsertId() {
                return mysql_insert_id($this->database);
        }
}
?>
