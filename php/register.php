<?php
header('Content-Type: application/json');

// MySQL
$mysqli = new mysqli("localhost","root","","student");
// $mysqli = new mysqli(
//     "profilehub-db.c3i4o2cm07zf.ap-south-1.rds.amazonaws.com", 
//     "admin", 
//     "Nilaasri30062004", 
//     "profilehub");
if($mysqli->connect_error){
    error_log("MySQL Connect Error: ".$mysqli->connect_error);
    echo json_encode(["status"=>"error","msg"=>"Database connection failed"]);
    exit;
    //die(json_encode(["status"=>"error","msg"=>"MySQL Error: ".$mysqli->connect_error]));
}

// MongoDB
require '../vendor/autoload.php';
use MongoDB\Client;
try {
    $mongo = new Client("mongodb://localhost:27017");
    //$mongo = new Client("mongodb+srv://ProfileHub-db:Nilaa%402004@nilaasri.gwznodq.mongodb.net");
    //$mongo = new Client("mongodb+srv://sriselliprt_db_user:Nilaa@2004@profilehub.yvxrns6.mongodb.net/?retryWrites=true&w=majority&appName=ProfileHub");

    $profiles = $mongo->new_profiles->profiles;
}catch(Exception $e){
    die(json_encode(["status"=>"error","msg"=>"MongoDB Error: ".$e->getMessage()]));
}

// Get POST Data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirmPassword'] ?? '';
$dob = $_POST['dob'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$age = intval($_POST['age'] ?? 0);
$address = trim($_POST['address'] ?? '');
$gender = trim($_POST['gender'] ?? '');

// Validation
if(empty($name)||empty($email)||empty($password)){
    echo json_encode(["status"=>"error","msg"=>"Name, Email, Password required"]);
    exit;
}
if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
    echo json_encode(["status"=>"error","msg"=>"Invalid email"]);
    exit;
}
if($password!==$confirm){
    echo json_encode(["status"=>"error","msg"=>"Passwords do not match"]);
    exit;
}

// Hash password
$passHash = password_hash($password,PASSWORD_BCRYPT);

// MySQL Insert
$stmt = $mysqli->prepare("INSERT INTO users (email,password) VALUES (?,?)");
$stmt->bind_param("ss",$email,$passHash);
if($stmt->execute()){
    $userId = $stmt->insert_id;
    // MongoDB Insert
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
        echo json_encode(["status"=>"success","msg"=>"Registered successfully"]);
    }catch(Exception $e){
        $mysqli->query("DELETE FROM users WHERE id=$userId"); // rollback
        error_log("MongoDB Insert Error: ".$e->getMessage());
        echo json_encode(["status"=>"error","msg"=>"MongoDB insert failed"]);
    }
}else{
    echo json_encode(["status"=>"error","msg"=>"Email might already exist"]);
}

$stmt->close();
$mysqli->close();
?>
