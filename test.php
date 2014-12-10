<?php

ini_set('memory_limit', '64M');

error_reporting(E_ALL);

$markers = array();

for($i=0;$i<50000;$i++) {

	$markers[] = array(
		'id' => 'test',
		'lat' => rand(5000, 5500) / 100,
		'lon' => rand(400, 500) / 100
	);
}

echo json_encode($markers);