<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
//
header("Access-Control-Allow-Origin: https://guvi-intern-md3o.onrender.com");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// --- MySQL ---
$mysqli = new mysqli(
    getenv("MYSQL_HOST"),
    getenv("MYSQL_USER"),
    getenv("MYSQL_PASSWORD"),
    getenv("MYSQL_DB")
);
if($mysqli->connect_error){
    echo json_encode(["status"=>"error","msg"=>"MySQL connection failed"]);
    exit;
}

// --- Redis (optional) ---
$redis = new Redis();
try{
    $redis->connect(getenv("REDIS_HOST"), 6379);
}catch(Exception $e){
    echo json_encode(["status"=>"error","msg"=>"Redis connection failed: ".$e->getMessage()]);
    exit;
}

// --- Get POST Data ---
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
}else{
    echo json_encode(["status"=>"error","msg"=>"Invalid login"]);
}

$mysqli->close();
?>

