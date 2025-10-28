<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$mysqli = mysqli_init();
mysqli_ssl_set($mysqli, NULL, NULL, NULL, NULL, NULL);

if (!mysqli_real_connect(
    $mysqli,
    getenv("MYSQL_HOST"),
    getenv("MYSQL_USER"),
    getenv("MYSQL_PASSWORD"),
    getenv("MYSQL_DB"),
    3306,
    NULL,
    MYSQLI_CLIENT_SSL
)) {
    die("❌ Connection failed: " . mysqli_connect_error());
}

echo "✅ MySQL connected successfully!";
?>
