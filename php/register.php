<?php
header('Content-Type: application/json');
ini_set('display_errors', 0); // Hide warnings in production
error_reporting(E_ALL);

// Allow only your frontend domain (important for CORS)
header("Access-Control-Allow-Origin: https://guvi-intern-md3o.onrender.com");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// --- MySQL (Aiven Cloud) ---
$host = getenv('MYSQL_HOST');
$port = getenv('MYSQL_PORT') ?: 3306;
$user = getenv('MYSQL_USER');
$pass = getenv('MYSQL_PASSWORD');
$db   = getenv('MYSQL_DB');

$mysqli = mysqli_init();

// Enable SSL for Aiven MySQL
$mysqli->ssl_set(NULL, NULL, '/etc/ssl/certs/ca-certificates.crt', NULL, NULL);

if (!$mysqli->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    echo json_encode(['status' => 'error', 'msg' => 'MySQL connect failed: ' . $mysqli->connect_error]);
    exit;
}

// --- MongoDB Connection (Atlas) ---
require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

try {
    $mongoUri = getenv('MONGO_URL');
    $mongo = new Client($mongoUri);
    $profiles = $mongo->ProfileHub->profiles;
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'msg' => 'MongoDB connect failed: ' . $e->getMessage()]);
    exit;
}

// --- Redis Connection (Optional) ---
$redis = null;
$redisUrl = getenv('REDIS_URL');

if ($redisUrl) {
    try {
        $p = parse_url($redisUrl);
        $redis = new Redis();
        $redis->connect($p['host'], $p['port']);
        if (!empty($p['pass'])) {
            $redis->auth($p['pass']);
        }
    } catch (Exception $e) {
        error_log("Redis connection failed: " . $e->getMessage());
    }
}

// --- Get POST Data ---
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirmPassword'] ?? '';
$dob = $_POST['dob'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$age = intval($_POST['age'] ?? 0);
$address = trim($_POST['address'] ?? '');
$gender = trim($_POST['gender'] ?? '');

// --- Validation ---
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "msg" => "Name, Email, and Password are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "msg" => "Invalid email format"]);
    exit;
}

if ($password !== $confirm) {
    echo json_encode(["status" => "error", "msg" => "Passwords do not match"]);
    exit;
}

// --- Hash Password ---
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// --- MySQL Insert ---
$stmt = $mysqli->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $hashedPassword);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;

    // --- MongoDB Insert ---
    try {
        $profiles->insertOne([
            "userId" => $userId,
            "name" => $name,
            "email" => $email,
            "dob" => $dob,
            "contact" => $phone,
            "age" => $age,
            "address" => $address,
            "gender" => $gender,
            "created_at" => new MongoDB\BSON\UTCDateTime()
        ]);

        // --- Redis Cache (Optional) ---
        if ($redis) {
            $sessionKey = "session:user:$userId";
            $redis->setex($sessionKey, 3600, json_encode([
                'userId' => $userId,
                'email' => $email,
                'created' => time()
            ]));
        }

        echo json_encode(["status" => "success", "msg" => "Registered successfully"]);

    } catch (Exception $e) {
        // Rollback MySQL record if Mongo insert fails
        $mysqli->query("DELETE FROM users WHERE id=$userId");
        echo json_encode(["status" => "error", "msg" => "MongoDB insert failed: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "msg" => "Email already exists or MySQL insert failed"]);
}

$stmt->close();
$mysqli->close();
?>

