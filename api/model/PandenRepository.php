<?php

require('../vendor/autoload.php');

/**
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
class PandenRepository
{
    /**
     * @var string
     */
    private $url = 'http://bgtld-test.geostandaarden.nl/query/detailinfopand.json?subject=http://bag.kadaster.nl/id/pand/';

    public function getAll()
    {
        //TO DO
        //Get all "panden"
    }

    public function getSingle($bagId = '0546100000036042')
    {
        $file = file_get_contents($this->url . $bagId);

        $json = json_decode($file);

        $expanded = \ML\JsonLD\JsonLD::expand($json);
        $expanded = $expanded[0];

        print_r($expanded);die;

        $id = str_replace("http://bag.kadaster.nl/id/pand/", "", $expanded->{'@id'});

        $object = new stdClass();
        $object->id = $id;
        $object->url = $expanded->{'@id'};
        $object->bouwjaar = $this->getBagValue('bouwjaar', $expanded);
        //$object->gebruiksdoel = $this->getBagValue('gebruiksdoel', $expanded);
        $object->huisnummer = $this->getBagValue('huisnummer', $expanded);
        $object->straatnaam = $this->getBagValue('naamOpenbareRuimte', $expanded);
        $object->postcode = $this->getBagValue('postcode', $expanded);
        $object->stad = $this->getBagValue('woonplaatsnaam', $expanded);
        $object->status = $this->getBagValue('status', $expanded);

        $object->woz = new stdClass();
        //$object->woz->type = $this->getBgtValue('typeGebouw', $expanded);
        //$object->woz->waarde = $this->getBgtValue('waarde', $expanded);

        return $object;
    }

    private function getBagValue($value, $expanded){
        $bagLink = 'http://bag.kadaster.nl/def#';
        return $expanded->{$bagLink . $value}[0]->{'@value'};
    }

    private function getBgtValue($value, $expanded){
        $bgtLink = 'http://bgtld-test.geostandaarden.nl/woz/def#';
        return $expanded->{$bgtLink . $value}[0]->{'@value'};
    }


    public function getGeo($bagId)
    {
        //TO DO
        //Get lng + lat by bagId from MongoDB
    }
}
