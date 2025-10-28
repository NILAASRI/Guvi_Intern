<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Allow access from your frontend Render domain
header("Access-Control-Allow-Origin: https://guvi-intern-md3o.onrender.com");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// --- AIVEN MYSQL CONNECTION (SSL Secure) ---
$mysqli = mysqli_init();
mysqli_ssl_set($mysqli, NULL, NULL, NULL, NULL, NULL);

if (!mysqli_real_connect(
    $mysqli,
    getenv("MYSQL_HOST"),        // e.g., mysql-name-profilehub.aivencloud.com
    getenv("MYSQL_USER"),        // e.g., avnadmin
    getenv("MYSQL_PASSWORD"),    // your Aiven password
    getenv("MYSQL_DB"),          // e.g., student
    3306,                        // port
    NULL,
    MYSQLI_CLIENT_SSL
)) {
    echo json_encode([
        "status" => "error",
        "msg" => "MySQL connection failed: " . mysqli_connect_error()
    ]);
    exit;
}

// --- Get POST Data ---
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "msg" => "Email and Password are required"]);
    exit;
}

// --- Check user in MySQL ---
$stmt = $mysqli->prepare("SELECT id, password FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($result && password_verify($password, $result['password'])) {
    $sessionId = bin2hex(random_bytes(16)); // unique session token

    //Temporarily store session in PHP (optional - you can later use Redis)
    session_start();
    $_SESSION['sessionId'] = $sessionId;
    $_SESSION['userId'] = $result['id'];

    echo json_encode(["status" => "success", "sessionId" => $sessionId]);
} else {
    echo json_encode(["status" => "error", "msg" => "Invalid login"]);
}

$mysqli->close();
?>
