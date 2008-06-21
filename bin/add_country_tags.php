<?php

include('inc/config.php');
include('inc/db.php');

$countries = dbSelect('SELECT * FROM countries');
foreach($countries AS $country){
	
	$data[] = array('country_id' => $country['id'], 'tag' => $country['name']);
			
}

dbInsert('country_tags', $data);

?>