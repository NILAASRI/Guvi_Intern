<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

echo "<pre>";

// Test MySQL
$mysqli = new mysqli(getenv("MYSQL_HOST"), getenv("MYSQL_USER"), getenv("MYSQL_PASSWORD"), getenv("MYSQL_DB"));
if ($mysqli->connect_error) {
    die("MySQL failed: " . $mysqli->connect_error);
}
echo "MySQL Connected\n";

// Test MongoDB
try {
    $mongo = new Client(getenv("MONGO_URI"));
    $mongo->listDatabases();
    echo "MongoDB Connected\n";
} catch (Exception $e) {
    die("MongoDB failed: " . $e->getMessage());
}
