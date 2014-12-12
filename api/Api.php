<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once 'RestInterface.php';
require_once 'model/Freebase.php';
require_once 'model/MongoRepository.php';

/**
 * Api
 *
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
class Api extends RestInterface
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    protected function bomen()
    {
       $lat = $_GET['lat'];
       $lng = $_GET['lng'];

       $url = 'http://nieuwsinkaart.nl/geoserver/bgt/ows?service=WFS&version=2.0.0&request=GetFeature&typeName=bgt:bomenselectiegeo&outputformat=json&srsname=EPSG:4326&cql_filter=DWITHIN%28geometrie,POINT%28' . $lat . '%20' . $lng . '%29,0.0005,meters%29';

       if (file_exists('cache/' . md5($url))) {
           return json_decode(file_get_contents('cache/' . md5($url)));
       }
       
       $response = json_decode(file_get_contents($url), true);

       file_put_contents('cache/' . md5($url), json_encode($response));


       return $response;
    }

    protected function panden()
    {
        if ($this->method == 'GET') {
            if (isset($this->args[0])) {
                $url = 'http://bgtld-test.geostandaarden.nl/query/detailinfopand.json?subject=http://bag.kadaster.nl/id/pand/' . $this->args[0];
      
                if (file_exists('cache/' . md5($url))) {
                    return json_decode(file_get_contents('cache/' . md5($url)));
                }
         
                $data = json_decode(file_get_contents($url), true);
                $data = $data['@graph'];

                $response = [];
                $vestigingen = [];
                $gebruiksdoelen = [];
                $wozzes = [];
                foreach ($data as $obj) {
                    if (isset($obj['bagdef:bouwjaar'])) {
                        $response['bouwjaar'] = $obj['bagdef:bouwjaar'];
                    }
                    if (isset($obj['bagdef:gebruiksdoel'])) {
                        @$gebruiksdoelen[str_replace(' ', '_', $obj['bagdef:gebruiksdoel'])] += 1;
                    }
                    if (isset($obj['bagdef:status'])) {
                        $response['status'] = $obj['bagdef:status'];
                    }
                    if (isset($obj['rdfs:label'])) {
                        $vestigingen[] = ['id' => $obj['@id'], 'naam' => $obj['rdfs:label']];
                    }
                    if (isset($obj['ns3:waarde'])) {
                        $wozzes[] = ['waarde' => $obj['ns3:waarde'], 'type' => $obj['ns3:typeGebouw']];
                    }
                    if (isset($obj['bagdef:woonplaatsnaam'])) {
                        $response['stad'] = $obj['bagdef:woonplaatsnaam'];
                    }
                 
                    $client = new \MongoClient();
                    $db = $client->selectDB('bgtld');
                    $markers = $client->selectCollection($db, 'geometrics');
                    if ($obj = $markers->findOne(['id' => $obj['@id']])) {
                        $response['geometrie'] = $obj['coordinates'];
                    }

                }

                $response['verblijfsobjecten'] = $gebruiksdoelen;
                $response['vestigingen'] = $vestigingen;
                $response['woz'] = $wozzes;
      
                file_put_contents('cache/' . md5($url), json_encode($response));

                return $response;

            } else {
                $url = 'http://bgtld-test.geostandaarden.nl/query/zoekpand.json?minwaarde=' . (isset($_GET['minwaarde']) ? $_GET['minwaarde'] : 0) . '&maxwaarde=' . (isset($_GET['maxwaarde']) ? $_GET['maxwaarde'] : 9999999999999999) . '&minbouwjaar=' . (isset($_GET['minbouwjaar']) ? $_GET['minbouwjaar'] : 0) . '&maxbouwjaar=' . (isset($_GET['maxbouwjaar']) ? $_GET['maxbouwjaar'] : 9999999999999999) . '&ingebruik=' . (isset($_GET['ingebruik']) ? urlencode($_GET['ingebruik']) : '') . '&type=' . (isset($_GET['type']) ? $_GET['type'] : '');
                
                if (file_exists('cache/' . md5($url))) {
                    return json_decode(file_get_contents('cache/' . md5($url)));
                }

                $data = json_decode(file_get_contents($url), true);
                $response = [];

                $panden = [];

                if (isset($data['@id'])) {
                    $panden[] = $data;
                } elseif (isset($data['@graph'])) {
                    $panden = $data['@graph'];
                }

                foreach ($panden as $pand) {
                    $row['id'] = str_replace('http://bag.kadaster.nl/id/pand/', '', $pand['@id']);

                    $client = new \MongoClient();
                    $db = $client->selectDB('bgtld');
                    $markers = $client->selectCollection($db, 'markers');
                    if ($obj = $markers->findOne(['id' => $pand['@id']])) {

                        $row['lng'] = $obj['lng'];
                        $row['lat'] = $obj['lat'];
                    }

                    $response[] = $row;
                }

                file_put_contents('cache/' . md5($url), json_encode($response));
                return $response;
            }

        } else {
            return "Only accepts GET requests";
        }
    }

    /**
     * @return string
     */
    protected function freebase()
    {
        $freebase = new Freebase();

        if ($this->method == 'GET') {
            return $freebase->searchCompany($this->resource);
        } else {
            return "Only accepts GET requests";
        }
    }

    /**
     * @return string
     */
    protected function mongo($query = null)
    {
        $mongo = new MongoRepository('markers');

        return $mongo->get($query);
    }

}
