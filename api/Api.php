<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once 'RestInterface.php';
require_once 'Wkt.php';

/**
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 * @author Dimitri van Hees <dimitri@apiwise.nl>
 */
class Api extends RestInterface
{
    protected $cacheEnabled = true;

    public function __construct($request)
    {
        date_default_timezone_set('UTC');
        parent::__construct($request);
    }

    protected function checkCache($url)
    {
        if ($this->cacheEnabled && file_exists('cache/' . md5($url))) {
            return json_decode(file_get_contents('cache/' . md5($url)));
        }
    }

    protected function bomen()
    {
       $lat = $_GET['lat'];
       $lng = $_GET['lng'];

       $url = 'http://nieuwsinkaart.nl/geoserver/bgt/ows?service=WFS&version=2.0.0&request=GetFeature&typeName=bgt:bomenselectiegeo&outputformat=json&srsname=EPSG:4326&cql_filter=DWITHIN%28geometrie,POINT%28' . $lat . '%20' . $lng . '%29,0.0007,meters%29';

       $this->checkCache($url);

       $response = json_decode(file_get_contents($url), true);

       $this->cache($url, $response);

       return $response;
    }

    protected function panden()
    {
        if ($this->method == 'GET') {
            if (isset($this->args[0])) {

                $rawSparql = str_replace('@SUBJECT@', 'http://bag.kadaster.nl/id/pand/' . $this->args[0], '
CONSTRUCT
{
    <@SUBJECT@> <http://bag.kadaster.nl/def#bouwjaar> ?bouwjaar .
    <@SUBJECT@> <http://bag.kadaster.nl/def#status> ?status .
    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#pand>  <@SUBJECT@> .
    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#waarde> ?wozwaarde .
    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#typeGebouw> ?woztypegebouw .
    <@SUBJECT@> <http://bag.kadaster.nl/def#postcode> ?bagpostcode .
    <@SUBJECT@> <http://bag.kadaster.nl/def#huisnummer> ?baghuisnummer .
    <@SUBJECT@> <http://bag.kadaster.nl/def#naamOpenbareRuimte> ?straatnaam .
    <@SUBJECT@> <http://bag.kadaster.nl/def#woonplaatsnaam> ?woonplaatsnaam .
    <@SUBJECT@> <http://bgtld-test.geostandaarden.nl/woz/def#verblijfsobject> ?verblijfsobject .
    ?verblijfsobject <http://bag.kadaster.nl/def#gebruiksdoel> ?vbogebruiksdoel .
    ?nhrvestigingid rdfs:label ?bedrijfsnaam .
    ?bgtobject <http://bgtld-test.geostandaarden.nl/bgt/def#isBagObject> <@SUBJECT@> .
    ?bgtobject <http://www.w3.org/2003/01/geo/wgs84_pos#geometry> ?geometry .
}
WHERE {
    <@SUBJECT@> <http://bag.kadaster.nl/def#bouwjaar> ?bouwjaar .
    <@SUBJECT@> <http://bag.kadaster.nl/def#status> ?status .

    OPTIONAL {
        ?bgtpand <http://bgtld-test.geostandaarden.nl/bgt/def#isBagObject> <@SUBJECT@> .
    }

    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#pand>  <@SUBJECT@> .
    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#waarde> ?wozwaarde .
    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#typeGebouw> ?woztypegebouw .
    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#nummeraanduiding> ?woznummeraanduiding .
    ?woznummeraanduiding <http://bag.kadaster.nl/def#postcode> ?bagpostcode .
    ?woznummeraanduiding <http://bag.kadaster.nl/def#huisnummer> ?baghuisnummer .
    ?woznummeraanduiding <http://bag.kadaster.nl/def#gerelateerdeOpenbareRuimte> ?openbareruimte .
    ?openbareruimte <http://bag.kadaster.nl/def#naamOpenbareRuimte> ?straatnaam .
    ?openbareruimte <http://bag.kadaster.nl/def#woonplaatsOpenbareRuimte> ?woonplaats .

    OPTIONAL {
        ?woonplaats <http://bag.kadaster.nl/def#woonplaatsnaam> ?woonplaatsnaam .
    }

    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#verblijfsobject> ?verblijfsobject .

    OPTIONAL {
        ?verblijfsobject <http://bag.kadaster.nl/def#gebruiksdoel> ?vbogebruiksdoel .
        ?verblijfsobject <http://bag.kadaster.nl/def#status> ?vbostatus .
    }

    OPTIONAL {
        ?nhrvestigingid <http://bgtld-test.geostandaarden.nl/nhr/def#nummeraanduiding> ?woznummeraanduiding .
        ?nhrvestigingid rdfs:label ?bedrijfsnaam .
    }

    ?bgtobject <http://bgtld-test.geostandaarden.nl/bgt/def#isBagObject> <@SUBJECT@> .
    ?bgtobject <http://www.w3.org/2003/01/geo/wgs84_pos#geometry> ?geometry
}');

                $url = 'http://almere.pilod.nl/sparql?format=' . urlencode('application/ld+json') . '&query=' . urlencode($rawSparql);

                $this->checkCache($url);

                $data = json_decode(file_get_contents($url), true)['@graph'];

                $vestigingen = [];
                $gebruiksdoelen = [];
                $wozzes = [];
                $seperations = [];

                foreach ($data as $s => $array) {
                    foreach ($array as $key => $val) {

                        @$stringValue = $val[0]['@value'];

                        switch ($key) {
                            case 'http://bag.kadaster.nl/def#gebruiksdoel':
                                @$gebruiksdoelen[str_replace(' ', '_', $stringValue)] += 1;
                                break;
                            case 'http://www.w3.org/2000/01/rdf-schema#label':
                                $id = $data[$s]['@id'];
                                $vestigingen[$s] = ['id' => $id, 'naam' => $stringValue, 'nvwaControles' => $this->checkNvwa($id)];
                                break;
                            case 'http://bgtld-test.geostandaarden.nl/woz/def#waarde':
                                $wozzes[$s]['waarde'] = $stringValue;
                                break;
                            case 'http://bgtld-test.geostandaarden.nl/woz/def#typeGebouw':
                                $wozzes[$s]['type'] = $stringValue;
                                break;
                            case 'http://bgtld-test.geostandaarden.nl/woz/def#pand':
                                $wozzes[$s]['id'] = $data[$s]['@id'];
                                break;
                            case 'http://www.w3.org/2003/01/geo/wgs84_pos#geometry':
                                $response['geoJson'] = $this->wkt2geoJson($stringValue);
                                $response['wkt'] = $stringValue;
                                break;
                            case 'http://bag.kadaster.nl/def#bouwjaar':
                                $response['bouwjaar'] = $stringValue;
                                break;
                            case 'http://bag.kadaster.nl/def#status':
                                $response['status'] = $stringValue;
                                break;
                            case 'http://bag.kadaster.nl/def#woonplaatsnaam':
                                $response['stad'] = $stringValue;
                                break;
                            case 'http://bag.kadaster.nl/def#postcode':
                                $response['postcode'] = $stringValue;
                                break;
                            case 'http://bag.kadaster.nl/def#naamOpenbareRuimte':
                                $response['straat'] = $stringValue;
                                break;
                            case 'http://bag.kadaster.nl/def#huisnummer':
                                $response['huisnummer'] = $stringValue;
                                break;
                        }
                    }
                }

                $vestigingen = array_values($vestigingen);
                $wozzes = array_values($wozzes);

                $response['definition'] = $this->parseDefinition('http://data.stelselvanbasisregistraties.nl/bgt/doc/concept/Pand');
                $response['verblijfsobjecten'] = $gebruiksdoelen;
                $response['vestigingen'] = $vestigingen;
                $response['woz'] = $wozzes;
                $response['sparql'] = $rawSparql;

$scheidingRawSparql = sprintf("SELECT ?scheiding  ?geoScheiding ?lokaalId
WHERE {
        ?bgtobject <http://bgtld-test.geostandaarden.nl/bgt/def#isBagObject> <%s> .
        ?bgtobject <http://www.w3.org/2003/01/geo/wgs84_pos#geometry> ?geoPand .

        ?scheiding rdf:type <http://bgtld-test.geostandaarden.nl/bgt/def#Scheiding> .
        ?scheiding <http://bgtld-test.geostandaarden.nl/bgt/def#lokaalId> ?lokaalId .
        ?scheiding geo:geometry ?geoScheiding .
        FILTER (bif:st_intersects (?geoScheiding, ?geoPand, 0.0005))
    }", 'http://bag.kadaster.nl/id/pand/' . $this->args[0]);

                $scheidingUrl = 'http://almere.pilod.nl/sparql?format=application%2Fsparql-results%2Bjson&query=' . urlencode($scheidingRawSparql);
                $data = json_decode(file_get_contents($scheidingUrl), true)['results']['bindings'];
                foreach ($data as $seperation) {
                    $seperations['results'][] = [
                        'type' => 'Feature',
                        'properties' => [
                            'id' => $seperation['scheiding']['value'],
                            'lokaalId' => $seperation['lokaalId']['value'],
                        ],
                        'geometry' => $this->wkt2geoJson($seperation['geoScheiding']['value'])
                    ];
                }

                $seperations['sparql'] = $scheidingRawSparql;
                $seperations['definition'] = $this->parseDefinition('http://data.stelselvanbasisregistraties.nl/bgt/doc/concept/Scheiding');

                $response['seperations'] = $seperations;

                $this->cache($url, $response);

                return $response;

            } else {

                $rawSparql = sprintf('SELECT DISTINCT ?pand ?geometry
WHERE {
    ?pand a <http://bag.kadaster.nl/def#Pand> .
    ?pand <http://bag.kadaster.nl/def#bouwjaar> ?bouwjaar .
    ?pand <http://bag.kadaster.nl/def#status> ?status .
    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#pand> ?pand .
    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#waarde> ?waarde .
    ?wozobject <http://bgtld-test.geostandaarden.nl/woz/def#verblijfsobject> ?vbo .
    ?vbo <http://bag.kadaster.nl/def#gebruiksdoel> ?vbogebruiksdoel .
    ?bgtobject <http://bgtld-test.geostandaarden.nl/bgt/def#isBagObject> ?pand .
    ?bgtobject <http://www.w3.org/2003/01/geo/wgs84_pos#geometry> ?geometry .

    FILTER(xsd:decimal(?waarde) > xsd:decimal(%d) ) .
    FILTER(xsd:decimal(?waarde) < xsd:decimal(%d) ) .
    FILTER(xsd:decimal(?bouwjaar) > xsd:decimal(%d) ) .
    FILTER(xsd:decimal(?bouwjaar) < xsd:decimal(%d) ) .
    FILTER regex(?vbogebruiksdoel, "%s", "i") .
    FILTER regex(?status, "%s" , "i")
}
limit 2000',
                    (isset($_GET['minwaarde']) ? $_GET['minwaarde'] : 0),
                    (isset($_GET['maxwaarde']) ? $_GET['maxwaarde'] : 9999999999999999),
                    (isset($_GET['minbouwjaar']) ? $_GET['minbouwjaar'] : 0),
                    (isset($_GET['maxbouwjaar']) ? $_GET['maxbouwjaar'] : 9999999999999999),
                    (isset($_GET['type']) ? $_GET['type'] : ''),
                    (isset($_GET['status']) ? $_GET['status'] : '')
                );

                $url = 'http://almere.pilod.nl/sparql?format=application%2Fsparql-results%2Bjson&query=' . urlencode($rawSparql);

                $this->checkCache($url);

                $data = json_decode(file_get_contents($url), true)['results']['bindings'];
                $response['results'] = [];

                foreach ($data as $record) {
                    $row['id'] = str_replace('http://bag.kadaster.nl/id/pand/', '', $record['pand']['value']);
                    $row['geoJson'] = $this->wkt2geoJson($record['geometry']['value']);

                    $response['results'][] = $row;
                }

                $response['sparql'] = $rawSparql;

                $this->cache($url, $response);

                return $response;
            }

        } else {
            return "Only accepts GET requests";
        }
    }

    protected function checkNvwa($id)
    {
        $rawSparql = sprintf("CONSTRUCT {
    ?nvwacontrole <http://data.nvwa.nl/controles/def#kvkVestigingsnummer> ?nvwavestiging .
    ?nvwacontrole <http://www.w3.org/2000/01/rdf-schema#label> ?nvwabedrijf .
    ?nvwacontrole <http://data.nvwa.nl/controles/def#objectHoofdCategorie> ?hoofdCategorie .
    ?nvwacontrole <http://data.nvwa.nl/controles/def#datumgrondslag> ?datumGrondslag .
    ?nvwacontrole <http://data.nvwa.nl/controles/def#oordeel> ?oordeel .
    ?nvwacontrole <http://data.nvwa.nl/controles/def#grondslagOordeel> ?grondslagOordeel .
}
WHERE {
    ?nvwacontrole a <http://data.nvwa.nl/controles/def#Controle> .
    ?nvwacontrole <http://www.w3.org/2000/01/rdf-schema#label> ?nvwabedrijf .
    ?nvwacontrole <http://data.nvwa.nl/controles/def#objectHoofdCategorie> ?hoofdCategorie .
    ?nvwacontrole <http://data.nvwa.nl/controles/def#grondslagOordeel> ?grondslagOordeel .
    ?nvwacontrole <http://data.nvwa.nl/controles/def#datumgrondslag> ?datumGrondslag .
    ?nvwacontrole <http://data.nvwa.nl/controles/def#oordeel> ?oordeel .
    ?nvwacontrole <http://data.nvwa.nl/controles/def#kvkVestigingsnummer> ?nvwavestiging

    BIND(REPLACE(str(<%s>), '([^1-9]+[0])','') AS ?nhrid)
    FILTER regex(?nvwavestiging,?nhrid)
}", $id);

        $url = 'http://almere.pilod.nl/sparql?format=' . urlencode('application/ld+json') . '&query=' . urlencode($rawSparql);

        $data = json_decode(file_get_contents($url), true);
        if (count($data) == 0) {
            return $data;
        }

        $controles = [];
        foreach ($data['@graph'] as $controle) {
            $controles[] = [
                'id' => $controle['@id'],
                'date' => date('d-m-Y', strtotime($controle['http://data.nvwa.nl/controles/def#datumgrondslag'][0]['@value'])),
                'grondslagOordeel' => $controle['http://data.nvwa.nl/controles/def#grondslagOordeel'][0]['@value'],
                'objectHoofdCategorie' => $controle['http://data.nvwa.nl/controles/def#objectHoofdCategorie'][0]['@value'],
                'oordeel' => $controle['http://data.nvwa.nl/controles/def#oordeel'][0]['@value'],
            ];
        }

        return ['results' => $controles, 'sparql' => $rawSparql];
    }

    protected function parseDefinition($id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: rdf+xml']);
        $response = curl_exec ($ch);
        curl_close ($ch);

        $xml = simplexml_load_string($response);

        $definition['id'] = $id;
        $definition['label'] = (string) $xml->xpath('//def:naam')[0];
        $definition['source'] = (string) $xml->xpath('//def:herkomst')[0];
        $definition['text'] = (string) $xml->xpath('//def:definitie')[0];

        return $definition;
    }

    protected function wkt2geoJson($wkt)
    {
        $geom = WKT::load($wkt);
        $geoJson = json_decode($geom->toGeoJSON());

        return $geoJson;
    }

    protected function cache($url, $response)
    {
        if ($this->cacheEnabled == true) {
            file_put_contents('cache/' . md5($url), json_encode($response));
        }
    }
}
