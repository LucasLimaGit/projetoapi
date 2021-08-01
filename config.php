<?php 
require 'environment.php';

global $config;
$config = array();

if(ENVIRONMENT == 'development') {
    $config['dbname'] = 'api_rest';
    $config['host'] = 'localhost';
    $config['dbuser'] = 'root';
    $config['dbpass'] = 'root';
    $config['jwt_scret_key'] = 'lucasL!ma1602';
} else {
    $config['dbname'] = 'api_rest';
    $config['host'] = 'localhost';
    $config['dbuser'] = 'root';
    $config['dbpass'] = 'root';
    $config['jwt_scret_key'] = 'lucasL!ma1602';
}

global $db;
try {
    $db = new PDO("mysql:dbname=".$config['dbname'].";host=".$config['host'], $config['dbuser'], $config['dbpass']);
} catch(PDOException $e) {
    echo "ERRO: ".$e->getMessage();
    exit;
}
