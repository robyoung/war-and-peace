<?php

include('inc/config.php');
include('inc/db.php');
include('lib/MultiMap.php');

$countries = dbSelect('SELECT * FROM countries');
foreach($countries AS $country){
	
	$map = MultiMap::create();
	$coords = $map->getPoint($country['name']);
	
	$data = array('lat' => $coords[0], 'long' => $coords[1]);
	
	dbUpdate('countries', $data, 'id=' . $country['id']);
		
}

?>