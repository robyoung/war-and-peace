<?php

require('inc/config.php');
require('inc/db.php');

$handle = fopen($config['path'] . 'data/countries2.csv', "r");while (($data = fgetcsv($handle)) !== FALSE) {
		$countryId = dbInsert('countries', array('name' => $data[0]));
	
	$tags = array();
	for($i=1; $i<count($data)-1; $i++){
		if(!empty($data[$i])) $tags[] = array('tag' => $data[$i], 'country_id' => $countryId);
	}
	
	if(!empty($tags)) dbInsert('country_tags', $tags);}
fclose($handle);

?>