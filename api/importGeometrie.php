<?php
ini_set('memory_limit', '512M');
require_once 'model/MongoRepository.php';

$collection = new MongoRepository('geometrics');

$url = 'http://nieuwsinkaart.nl/rdfgeo/data/bgt/pandgeometrielatlon.json';

$client = new \MongoClient();
$db = $client->selectDB('bgtld');
$db->dropCollection('geometrics');
$db->createCollection('geometrics');
$collection = $db->selectCollection('geometrics');

$data = json_decode(file_get_contents($url), true);

foreach ($data['panden'] as $pand) {
    $doc = new stdClass();
    $doc->id = $pand['id'];
    $doc->coordinates = $pand['coordinates'];
    $collection->insert($doc);    
}
