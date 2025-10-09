<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// ----------------- MySQL Connection -----------------
$host = getenv('MYSQL_HOST');
$port = getenv('MYSQL_PORT');
$user = getenv('MYSQL_USER');
$pass = getenv('MYSQL_PASSWORD');
$db   = getenv('MYSQL_DB');

$mysqli = mysqli_init();
$mysqli->ssl_set(NULL, NULL, '/etc/ssl/certs/ca-certificates.crt', NULL, NULL); 
if (!$mysqli->real_connect($host, $user, $pass, $db, $port)) {
    echo json_encode(['status'=>'error','msg'=>'MySQL connect failed: '.$mysqli->connect_error]);
    exit;
}

// ----------------- MongoDB Connection -----------------
require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

$mongoUri = getenv('MONGO_URL'); // Use your MongoDB Atlas URI
try {
    $mongo = new Client($mongoUri);
    $profiles = $mongo->ProfileHub->profiles; // change DB name if different
} catch(Exception $e) {
    echo json_encode(['status'=>'error','msg'=>'MongoDB connect failed: '.$e->getMessage()]);
    exit;
}

// ----------------- Redis Connection -----------------
$redisUrl = getenv('REDIS_URL'); // redis://host:port
$redis = null;
if($redisUrl){
    $p = parse_url($redisUrl);
    try {
        $redis = new Redis();
        $redis->connect($p['host'], $p['port']);
        if(!empty($p['pass'])) $redis->auth($p['pass']);
    } catch(Exception $e) {
        // Redis is optional, warn but don't stop registration
        error_log("Redis connection failed: ".$e->getMessage());
    }
}

// ----------------- POST Data -----------------
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirmPassword'] ?? '';
$dob = $_POST['dob'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$age = intval($_POST['age'] ?? 0);
$address = trim($_POST['address'] ?? '');
$gender = trim($_POST['gender'] ?? '');

// ----------------- Validation -----------------
if(empty($name) || empty($email) || empty($password)){
    echo json_encode(["status"=>"error","msg"=>"Name, Email, Password required"]);
    exit;
}
if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
    echo json_encode(["status"=>"error","msg"=>"Invalid email"]);
    exit;
}
if($password !== $confirm){
    echo json_encode(["status"=>"error","msg"=>"Passwords do not match"]);
    exit;
}

// ----------------- Hash password -----------------
$passHash = password_hash($password,PASSWORD_BCRYPT);

// ----------------- MySQL Insert -----------------
$stmt = $mysqli->prepare("INSERT INTO users (email,password) VALUES (?,?)");
$stmt->bind_param("ss",$email,$passHash);
if($stmt->execute()){
    $userId = $stmt->insert_id;

    // ----------------- MongoDB Insert -----------------
    try{
        $profiles->insertOne([
            "userId"=>$userId,
            "name"=>$name,
            "email"=>$email,
            "dob"=>$dob,
            "contact"=>$phone,
            "age"=>$age,
            "address"=>$address,
            "gender"=>$gender,
            "created_at"=>new MongoDB\BSON\UTCDateTime()
        ]);

        // ----------------- Redis Session Example -----------------
        if($redis){
            $sessionKey = "session:user:$userId";
            $redis->setex($sessionKey, 3600, json_encode([
                'userId'=>$userId,
                'email'=>$email,
                'created'=>time()
            ]));
        }

        echo json_encode(["status"=>"success","msg"=>"Registered successfully"]);
    }catch(Exception $e){
        // rollback MySQL if MongoDB fails
        $mysqli->query("DELETE FROM users WHERE id=$userId");
        echo json_encode(["status"=>"error","msg"=>"MongoDB insert failed: ".$e->getMessage()]);
    }
}else{
    echo json_encode(["status"=>"error","msg"=>"Email might already exist or MySQL insert failed"]);
}

$stmt->close();
$mysqli->close();
?>

