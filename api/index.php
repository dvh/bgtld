<?php

require_once 'Api.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);

try {
    $api = new Api($_REQUEST['request']);
    echo json_encode($api->processAPI());
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}
