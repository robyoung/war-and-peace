<?php

$dbQueryCount = 0;
$dbSelectCount = 0;
$dbLink = 0;

$dblocationkhkhkjh = 0;
$dbusername_k798hkhjkh = 0;
$dbpassworddslfjdkl83adc = 0;
$dbnameklj = 0;

function dbConnect($location, $username, $password, $name)
{
	global $dblocationkhkhkjh;
	global $dbusername_k798hkhjkh;
	global $dbpassworddslfjdkl83adc;
	global $dbnameklj;
	
	$dblocationkhkhkjh = $location;
	$dbusername_k798hkhjkh = $username;
	$dbpassworddslfjdkl83adc = $password;
	$dbnameklj = $name;
}

//delay connection until when you actually need it
function _dbDelayedConnect()
{
    global $dbLink, $dblocationkhkhkjh;
    $dbLink = new PDO('sqlite:'.$dblocationkhkhkjh);
}

function _getLink()
{
    global $dbLink;
    if (!$dbLink) _dbDelayedConnect();
    return $dbLink;
}

function dbEnsureConnected()
{
    return true;
}

function dbQuery($query)
{
    global $dbQueryCount;

    $dbLink = _getLink();
    
    $dbQueryCount++;
    return $dbLink->exec($query);
}

function dbSelect($query, $one_row=false)
{
    global $dbSelectCount;
    $dbLink = _getLink();

    $result = $dbLink->query($query);

    return $one_row ? $result->fetch() : $result->fetchAll();
}

function dbInsert($table, $values)
{
    $sql = "INSERT INTO " . $table . " (" . implode(', ', array_keys($values)) . ') VALUES (' . implode(', ', array_fill(0, count($values), '?')) . ')';
    $dbLink = _getLink();
    $stmt   = $dbLink->prepare($sql);
    $stmt->execute(array_values($values));
    return $dbLink->lastInsertId();
}

function dbUpdate($table, $values, $where)
{
    $upd    = implode(', ', array_map(create_function('$a', 'return $a . "=?";'), array_keys($values)));
    $sql    = "UPDATE $table SET " . $upd . ($where?' WHERE ' . $where:'');
    $dbLink = _getLink();
    $stmt   = $dbLink->prepare($sql);
    $stmt->execute(array_values($values));
    return $stmt->rowCount();
}
