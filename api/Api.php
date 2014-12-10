<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once 'RestInterface.php';
require_once 'model/Freebase.php';
require_once 'model/PandRepository.php';
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

    /**
     * @return string
     */
    protected function test()
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
    protected function freebase()
    {
        $freebase = new Freebase();
        $result = $freebase->search($this->resource);

        $test = $freebase->image("/m/021z5y");

        $result = json_encode($result);

        echo $test;

        if ($this->method == 'GET') {
            return;
        } else {
            return "Only accepts GET requests";
        }
    }

    /**
     * @return string
     */
    protected function mongo()
    {
        $pand = new PandRepository();
        var_dump($pand->get());
    }



}
