<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once 'RestInterface.php';
require_once 'model/Freebase.php';
require_once 'model/MongoRepository.php';
require_once 'model/PandenRepository.php';

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

    protected function panden()
    {
        if ($this->method == 'GET') {

            if (isset($this->args[0])) {
                $url = 'http://dennis.dev.freshheads.local/api-toolkit/web/app_debug.php/mock/bgtld/panden/' . $this->args[0];
            } else {
                $url = 'http://dennis.dev.freshheads.local/api-toolkit/web/app_debug.php/mock/bgtld/panden';
            }

            return json_decode(file_get_contents($url), true);

        } else {
            return "Only accepts GET requests";
        }
    }

    protected function vestigingen()
    {
        if ($this->method == 'GET') {
            return "Success";
        } else {
            return "Only accepts GET requests";
        }
    }

    /**
     * @return string
     */
    protected function test()
    {
        if ($this->method == 'GET') {
            $repo = new PandenRepository();

            return $repo->getSingle();
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
    protected function mongo()
    {
        $mongo = new MongoRepository('markers');

        return $mongo->get();
    }

}
