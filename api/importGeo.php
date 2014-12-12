<?php

require_once 'model/MongoRepository.php';

$url = 'http://nieuwsinkaart.nl/geoserver/bgt/wfs?request=GetFeature&typename=pandcentroids&version=2.0.0&srsname=EPSG:4326&service=WFS&outputformat=json';

$data = json_decode(file_get_contents($url), true);

$collection = [];
foreacH ($data['features'] as $location) {
    $object = new stdClass();
    $object->id = str_replace('pandcentroids.', '', $location['id']);;
    $object->lng = $location['geometry']['coordinates'][0];
    $object->lat = $location['geometry']['coordinates'][1];

    $collection[] = $object;

}

$markers = new MongoRepository('markers');
$markers->post($collection);

echo "Geometry loaded successfully";
