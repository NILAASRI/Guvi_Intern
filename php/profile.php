<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);

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

// --- Redis ---
$redis = new Redis();
try{
    $redis->connect(getenv("REDIS_HOST"), 6379);
}catch(Exception $e){
    echo json_encode(["status"=>"error","msg"=>"Redis connection failed: ".$e->getMessage()]);
    exit;
}

// --- MongoDB ---
require '../vendor/autoload.php';
use MongoDB\Client;
try{
    $mongo = new Client(getenv("MONGO_URI"));
    $profiles = $mongo->new_profiles->profiles;
}catch(Exception $e){
    echo json_encode(["status"=>"error","msg"=>"MongoDB connection failed: ".$e->getMessage()]);
    exit;
}

// --- Get JSON POST Data ---
$input = json_decode(file_get_contents('php://input'), true);
$sessionId = $input['sessionId'] ?? '';
$action = $input['action'] ?? '';

if(!$sessionId){
    echo json_encode(["status"=>"error","msg"=>"Session ID missing"]);
    exit;
}

// --- Redis session check ---
$userId = $redis->get($sessionId);
if(!$userId){
    echo json_encode(["status"=>"error","msg"=>"Session expired"]);
    exit;
}

// --- Fetch profile ---
if($action === "fetch"){
    $stmt = $mysqli->prepare("SELECT email FROM users WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$result){
        echo json_encode(["status"=>"error","msg"=>"User not found in MySQL"]);
        exit;
    }

    $profile = $profiles->findOne(["userId"=>intval($userId)]);
    if(!$profile){
        echo json_encode(["status"=>"error","msg"=>"User profile not found in MongoDB"]);
        exit;
    }

    echo json_encode([
        "status"=>"success",
        "data"=>[
            "id"=>$userId,
            "email"=>$result['email'],
            "name"=>$profile['name'] ?? "",
            "dob"=>$profile['dob'] ?? "",
            "contact"=>$profile['contact'] ?? "",
            "age"=>$profile['age'] ?? "",
            "address"=>$profile['address'] ?? "",
            "gender"=>$profile['gender'] ?? ""
        ]
    ]);
    exit;
}

// --- Update profile ---
if($action === "update"){
    $updateData = [
        "name"=>$input['name'] ?? '',
        "dob"=>$input['dob'] ?? '',
        "contact"=>$input['contact'] ?? ''
    ];

    try{
        $result = $profiles->updateOne(
            ["userId"=>intval($userId)],
            ['$set'=>$updateData]
        );

        if($result->getModifiedCount() > 0){
            echo json_encode(["status"=>"success","msg"=>"Profile updated successfully"]);
        }else{
            echo json_encode(["status"=>"warning","msg"=>"No changes made"]);
        }
    }catch(Exception $e){
        echo json_encode(["status"=>"error","msg"=>"MongoDB update failed: ".$e->getMessage()]);
    }
    exit;
}

echo json_encode(["status"=>"error","msg"=>"Invalid action"]);
?>
