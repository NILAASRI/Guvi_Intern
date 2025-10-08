<?php
ini_set('display_errors',1); // temporarily for debugging
error_reporting(E_ALL);
header('Content-Type: application/json');

try {
    $host = getenv('MYSQL_HOST');
    $port = getenv('MYSQL_PORT');
    $user = getenv('MYSQL_USER');
    $pass = getenv('MYSQL_PASSWORD');
    $db   = getenv('MYSQL_DB');

    $mysqli = mysqli_init();
    $mysqli->ssl_set(NULL, NULL, '/etc/ssl/certs/ca-certificates.crt', NULL, NULL);
    if(!$mysqli->real_connect($host,$user,$pass,$db,$port, NULL, MYSQLI_CLIENT_SSL)){
        throw new Exception("MySQL connect failed: ".$mysqli->connect_error);
    }

    require __DIR__.'/../vendor/autoload.php';
    $mongo = new MongoDB\Client(getenv('MONGO_URL'));
    $mongo->listDatabases(); // test connection

    echo json_encode(['status'=>'ok','msg'=>'All connections are OK']);
} catch(Exception $e){
    echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
}
