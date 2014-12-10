<?php

ini_set('memory_limit', '64M');

error_reporting(E_ALL);

$markers = array();

for($i=0;$i<1000;$i++) {

	$markers[] = array(
		'id' => hash('sha256', uniqid()),
		'lat' => 52.175616047410195 + (rand(0, 12000) / 100000),
		'lng' => 4.32861328125 + (rand(0, 10000) / 100000)
	);
}

echo json_encode(array('results' => $markers));