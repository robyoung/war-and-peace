<?php

$dbQueryCount = 0;
$dbSelectCount = 0;
$dbLink = 0;

$dblocationkhkhkjh = 0;
$dbusername_k798hkhjkh = 0;
$dbpassworddslfjdkl83adc = 0;
$dbnameklj = 0;

/**
 * This file creates the database functions
 * @param string $query The sql query to be performed
 */
 
function dbConnect($dblocation, $dbusername, $dbpassword, $dbname){
	global $dblocationkhkhkjh;
	global $dbusername_k798hkhjkh;
	global $dbpassworddslfjdkl83adc;
	global $dbnameklj;
	
	$dblocationkhkhkjh = $dblocation;
	$dbusername_k798hkhjkh = $dbusername;
	$dbpassworddslfjdkl83adc = $dbpassword;
	$dbnameklj = $dbname;
}

//delay connection until when you actually need it
function _dbDelayedConnect(){
	global $dblocationkhkhkjh;
	global $dbusername_k798hkhjkh;
	global $dbpassworddslfjdkl83adc;
	global $dbnameklj;
	global $dbLink;
	
	$dbLink = mysql_connect ($dblocationkhkhkjh, $dbusername_k798hkhjkh, $dbpassworddslfjdkl83adc) or die (mysql_error());
	mysql_select_db($dbnameklj);
}

function dbEnsureConnected(){
	global $dbLink;
	if(!$dbLink) return _dbDelayedConnect();
	return true;
}

function dbSelectDb($db){
	mysql_select_db($db);
}

function dbReselectDb(){
	global $dbnameklj;
	dbSelectDb($dbnameklj);
}

function dbQuery($query){

	global $dbLink;
	global $dbQueryCount;

	if(!$dbLink) _dbDelayedConnect();

	$result = mysql_query($query) or die(mysql_error() . $query);
	return @mysql_affected_rows($result);
	
	$dbQueryCount++;

}

function dbEscapeString($string){
	global $dbLink;
	if(!$dbLink) _dbDelayedConnect();
	return mysql_real_escape_string(stripslashes($string));
}

function dbSelect($query, $one_row=0){

	global $querystuff;
	global $dbSelectCount;

	$i = 0;
	
	// start php exec time
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$sqlbegintime = $time;
	//
	global $dbLink;
	if(!$dbLink) _dbDelayedConnect();
	
	$result = mysql_query($query) or die(mysql_error() . $query);
	
	
	// finish exec time
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$sqlendtime = $time;
	$sqltotaltime = ($sqlendtime - $sqlbegintime);
	//	
	//$querystuff .= round($sqltotaltime,4) . " - " . $query . "<br/>";
	
	
	$dbSelectCount++;
	
	// if there is only supposed to be one row, return it as the array
	$data = null;
	if($one_row){
	
		$data = mysql_fetch_array($result, MYSQL_ASSOC);
	
	}else{
	
  	while($mysqldata = mysql_fetch_array($result, MYSQL_ASSOC)){
  		$data[$i] = $mysqldata;
  		$i++;
  	}
	
	}
	
	return $data;

}

function dbInsert($table, $data, $ignoreDupe = false){
		
	$sql_string_fields = "";
	$sql_string_values = "";

	if(is_array($data)){
		foreach($data as &$data1){
			$embedded = is_array($data1);
			$fields = $embedded? $data1 : $data;
			break;
		}
		
		foreach($fields AS $dataid=>$value){
			if(!empty($sql_string_fields)) $sql_string_fields .= ', ';
			$sql_string_fields .= "`".dbEscapeString($dataid)."`";	
		}
		
		$values = '';
		
		if(!$embedded){
			foreach($fields as &$value){
				if($sql_string_values != '') $sql_string_values .= ', ';
				if(is_int($value))
					$sql_string_values .= $value;
				else
					$sql_string_values .= "'" . dbEscapeString($value) . "'";
			}
			$values = "VALUES ($sql_string_values)";
		}else{
			foreach($data as $fields){
				$sql_string_values = '';
				foreach($fields as $value){
					if($sql_string_values != '') $sql_string_values .= ', ';
					if(is_int($value))
						$sql_string_values .= $value;
					else
						$sql_string_values .= "'" . dbEscapeString($value) . "'";
				}
			
				if($values != '')
					$values .= ',';
				else
					$values = 'VALUES ';
				$values .= "($sql_string_values)";
			}
		}
		
		// insert details
		$sql = "INSERT INTO $table ($sql_string_fields) $values";
		
		global $dbLink;
		if(!$dbLink) _dbDelayedConnect();
		
		$result = mysql_query($sql) or ((!$ignoreDupe) && die('lib/db.php->dbInsert: ' . mysql_error() . stackTrace()));
		$lastid = mysql_insert_id();
	
		
	
		return $lastid;
	}
}

function dbUpdate($table, $data, $where){

	$sql_string = "";

	foreach($data AS $dataid=>&$value){
		if(!empty($sql_string)) $sql_string .= ', ';
		$sql_string .= ' `' . dbEscapeString($dataid) . "`='" . dbEscapeString($value) . "'";
	}
	
	$sql = "UPDATE $table SET $sql_string" . (($where != '')? " WHERE $where" : '');
	
	global $dbLink;
	if(!$dbLink) _dbDelayedConnect();

	$result = mysql_query($sql) or die('lib/db.php->dbUpdate: ' . mysql_error() . stackTrace());

	

	return mysql_affected_rows();
	
}

function dbOr($table, $data, $where){

	$sql_string = '';

	foreach($data AS $dataid=>&$value){
		if(!empty($sql_string)) $sql_string .= ', ';
		$id = dbEscapeString($dataid);
		$sql_string .= "$id=$id|" . dbEscapeString($value);
	}
	
	$sql = "UPDATE $table SET $sql_string" . (($where != '')? " WHERE $where" : '');
	return dbQuery($sql);

}

function dbMask($table, $data, $where){
	

	$sql_string = "";

	foreach($data AS $dataid=>&$value){
		if(!empty($sql_string)) $sql_string .= ', ';
		$id = dbEscapeString($dataid);
		$sql_string .= "$id=$id&" . dbEscapeString($value);
	}
	
	$sql = "UPDATE $table SET $sql_string" . (($where != '')? " WHERE $where" : '');
	
	global $dbLink;
	if(!$dbLink) _dbDelayedConnect();
	
	$result = mysql_query($sql) or die ('lib/db.php->dbMask: ' . mysql_error() . stackTrace());
	//echo($sql);
	
	
	return $result;
	
	return mysql_affected_rows();
}

function dbInc($table, $data, $where){
	
	
	$sql_string = "";

	foreach($data AS $dataid=>&$value){
		if(!empty($sql_string)) $sql_string .= ', ';
		$id = dbEscapeString($dataid);
		$sql_string .= "$id=$id" . (($value > 0)? '+' : '-' ) . dbEscapeString($value);
	}
	
	$sql = "UPDATE $table SET $sql_string" . (($where != '')? " WHERE $where" : '');
	
	global $dbLink;
	if(!$dbLink) _dbDelayedConnect();
	
	$result = mysql_query($sql) or die ('lib/db.php->dbUpdate: ' . mysql_error() . stackTrace());
	
	
	return $result;
}

function dbTotalRows(){
	global $dbLink;
	if(!$dbLink) _dbDelayedConnect();

	$sql = "SELECT FOUND_ROWS() AS total";
	$result = mysql_query($sql);
	$row_count = mysql_fetch_array($result);
	
	return $row_count['total'];
}

function dbWhere($field, $values, $operator='OR'){
	if(is_array($values))
		return ' ('.$field.'=\'' . implode("' $operator $field='", $values) . '\')';
	else
		return "$field=$values";
}

function dbFilterFields($legalFields, $fields){
	$result = array();
	foreach($legalFields as $field)
		$result[$field] = isset($fields[$field])? $fields[$field] : 0;
	return $result;
}

function dbLimit($count, $offset=0){
	if($count == 0) return '';
	$limit = $count;
	if($offset) $limit = $offset .','. $limit;
	return ' LIMIT ' . $limit;
}

function dbDelete($table, $where){
	dbQuery('DELETE FROM ' . $table . ' WHERE ' . $where);
}

function dbSelectAll($table, $fields = '*'){
	return dbSelect('SELECT ' . $fields . ' FROM ' . $table);
}

function dbGet($table, $id, $fields = '*'){
	return dbSelect('SELECT ' . $fields . ' FROM ' . $table . ' WHERE id=' . $id, true);
}

?>
