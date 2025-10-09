<?php
$servername = "mysql-name-profilehub.aivencloud.com";
$username = "avnadmin";
$password = "AVNS_uo6_8dHnxa...";
$dbname = "defaultdb";
$port = 22362;

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

if (!mysqli_real_connect($conn, $servername, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT)) {
    die(json_encode(["status" => "error", "msg" => "Database connection failed: " . mysqli_connect_error()]));
}
?>
