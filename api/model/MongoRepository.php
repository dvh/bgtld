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

        $this->db->dropCollection($this->collection);
        $this->db->createCollection($this->collection);
        $collection = $this->db->selectCollection($this->collection);

$totalItems = count($data);
$batches = ceil($totalItems/1000);

for ($offset = 0; $offset <= $batches; $offset ++) {    
    $limit = 1000;
   $insert = array_slice($data, 0, $limit);
//var_dump(count($insert));
    var_dump($collection->insert($insert));
}
        return true;

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
