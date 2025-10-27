<?php
session_start();
include("con_db.php");

$response = ["success"=>false, "message"=>""];

if(isset($_SESSION['username']) && $_SERVER['REQUEST_METHOD'] === "POST"){
    $current_username = $_SESSION['username'];
    $new_username = mysqli_real_escape_string($savienojums, $_POST['new_username']);
    $new_password = $_POST['new_password'];

    $updates = [];
    if($new_username !== $current_username) {
        $updates[] = "username='$new_username'";
    }
    if(!empty($new_password)){  
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $updates[] = "password='$hashed'";
    }

    if(!empty($updates)){
        $sql = "UPDATE net_users SET ".implode(",", $updates)." WHERE username='$current_username'";
        if(mysqli_query($savienojums, $sql)){
            if($new_username !== $current_username) $_SESSION['username'] = $new_username;
            $response["success"] = true;
            $response["message"] = "Konts veiksmīgi atjaunots!";
        } else {
            $response["message"] = "Kļūda: ".mysqli_error($savienojums);
        }
    } else {
        $response["message"] = "Nav veiktas izmaiņas.";
    }
}

echo json_encode($response);
