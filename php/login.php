<?php
header('Content-Type: application/json');

// --- MySQL connection ---
$mysqli = new mysqli("localhost", "root", "", "student");
// $mysqli = new mysqli(
//     "ProfileHub-db.c3i4o2cm07zf.ap-south-1.rds.amazonaws.com", 
//     "admin", 
//     "Nilaasri30062004", 
//     "profilehub");
if($mysqli->connect_error){
    die(json_encode(["status"=>"error","msg"=>"MySQL connection failed: ".$mysqli->connect_error]));
}

// --- Redis connection ---
$redis = new Redis();
try {
    $redis->connect('127.0.0.1', 6379);
    //$redis->connect('profilehub-redis-ecscsf.serverless.aps1.cache.amazonaws.com:6379');
} catch (Exception $e) {
    echo json_encode(["status"=>"error","msg"=>"Redis connection failed: ".$e->getMessage()]);
    exit;
}

// --- Get POST data safely ---
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if(!$email || !$password){
    echo json_encode(["status"=>"error","msg"=>"Email and Password are required"]);
    exit;
}

// --- Check user in MySQL ---
$stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if($result && password_verify($password, $result['password'])){
    $sessionId = bin2hex(random_bytes(16)); // unique session token

    // Store in Redis for 1 hour
    $redis->set($sessionId, $result['id']);
    $redis->expire($sessionId, 3600);

    echo json_encode(["status"=>"success", "sessionId"=>$sessionId]);
} else {
    echo json_encode(["status"=>"error", "msg"=>"Invalid login"]);
}

$mysqli->close();
?>
