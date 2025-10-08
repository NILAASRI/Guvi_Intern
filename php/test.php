<?php
header('Content-Type: application/json');
try {
    // Test MySQL
    $mysqli = mysqli_init();
    $mysqli->ssl_set(NULL,NULL,'/etc/ssl/certs/ca-certificates.crt',NULL,NULL);
    if(!$mysqli->real_connect(getenv('MYSQL_HOST'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'), getenv('MYSQL_DB'), getenv('MYSQL_PORT'), NULL, MYSQLI_CLIENT_SSL)){
        throw new Exception("MySQL connection failed: ".$mysqli->connect_error);
    }

    // Test MongoDB
    require __DIR__.'/vendor/autoload.php';
    $mongo = new MongoDB\Client(getenv('MONGO_URL'));
    $mongo->listDatabases();

    echo json_encode(['status'=>'ok','msg'=>'All connections OK']);
} catch(Exception $e){
    echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
}
