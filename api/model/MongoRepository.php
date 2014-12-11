<?php

/**
 * MongoRepository
 *
 * @author Dennis van Meel <dennis.van.meel@freshheads.com>
 */
class MongoRepository
{
    /**
     * @var string
     */
    private $collection;

    /**
     * @var MongoClient
     */
    private $client;

    /**
     * @var MongoDB
     */
    private $db;

    function __construct($collection)
    {
        $this->client = new \MongoClient();
        $this->db = $this->client->selectDB('bgtld');
        $this->collection = $collection;
    }

    public function post($data)
    {

    }

    public function get($query = [])
    {
        try {
            $mongoCollection = $this->db->selectCollection($this->collection);

            $cursor = $mongoCollection->find($query);

            $data = [];
            foreach ($cursor as $pointer) {
                unset($pointer['_id']);
                $data[] = $pointer;
            }

            $this->client->close(true);

            return $data;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}
