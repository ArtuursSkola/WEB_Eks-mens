<?php
header("Content-Type: application/json; charset=UTF-8");

session_start();
include("../../con_db.php");

$conn = new mysqli("localhost","grobina1_kliecis","gS8cBy218nhr@","grobina1_kliecis");
if($conn->connect_error){ http_response_code(500); echo json_encode(["error"=>"DB connect failed"]); exit; }

if (!isset($_SESSION['username']) || !isset($_SESSION['loma']) || !in_array($_SESSION['loma'], ['administrators', 'moderators'])){
    http_response_code(401); echo json_encode(["error"=>"Nepieciešama autorizācija"]); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if($method === 'GET'){
    if(isset($_GET['id'])){
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT id, username, vards, uzvards, email, telefons, loma FROM net_users WHERE id=? AND loma IN ('administrators','moderators')");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($row = $result->fetch_assoc()) echo json_encode($row, JSON_UNESCAPED_UNICODE);
        else { http_response_code(404); echo json_encode(["error"=>"Darbinieks nav atrasts"]); }
        $stmt->close();
    } else {
        $res = $conn->query("SELECT id, username, vards, uzvards, email, telefons, loma FROM net_users WHERE loma IN ('administrators','moderators') ORDER BY id DESC");
        $rows = []; while($row=$res->fetch_assoc()) $rows[]=$row;
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    }
}
elseif($method==='POST'){
    $data=json_decode(file_get_contents("php://input"),true);
    if(!isset($data['username'],$data['vards'],$data['uzvards'],$data['email'],$data['loma'],$data['password'])){
        http_response_code(400); echo json_encode(["error"=>"Nepieciešami dati"]); exit;
    }
    $pass_hashed=password_hash($data['password'],PASSWORD_DEFAULT);
    $telefons = $data['telefons'] ?? '';
    $stmt=$conn->prepare("INSERT INTO net_users (username,vards,uzvards,email,telefons,loma,password,created_at) VALUES (?,?,?,?,?,?,?,NOW())");
    $stmt->bind_param("sssssss",$data['username'],$data['vards'],$data['uzvards'],$data['email'],$telefons,$data['loma'],$pass_hashed);

    try { 
        if($stmt->execute()){ http_response_code(201); echo json_encode(["message"=>"Darbinieks izveidots"]); } 
        else { http_response_code(400); echo json_encode(["error"=>"Neizdevās izveidot darbinieku"]); } 
    } catch(mysqli_sql_exception $e){ 
        if($e->getCode()==1062){ http_response_code(400); echo json_encode(["error"=>"Šāds lietotājvārds jau eksistē!"]); }
        else { http_response_code(500); echo json_encode(["error"=>"Kļūda: ".$e->getMessage()]); }
    }
    $stmt->close(); exit;
}
elseif($method==='PUT'){
    if(!isset($_GET['id'])){
        http_response_code(400);
        echo json_encode(["error"=>"Nepieciešams ID"]);
        exit;
    }

    $id = (int)$_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);

$update_fields = "username=?, vards=?, uzvards=?, email=?, telefons=?, loma=?, last_edit_at=NOW()";
$params = [
    $data['username'],
    $data['vards'],
    $data['uzvards'],
    $data['email'],
    $data['telefons'] ?? '',
    $data['loma']
];
$types = "ssssss";

if(!empty($data['password'])){
    $update_fields .= ", password=?";
    $password_hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $params[] = $password_hashed;
    $types .= "s";
}

$update_fields .= " WHERE id=? AND loma IN ('administrators','moderators')";
$params[] = $id;
$types .= "i";

$stmt = $conn->prepare("UPDATE net_users SET $update_fields");
$stmt->bind_param($types, ...$params);

if($stmt->execute()){
    echo json_encode(["message"=>"Darbinieks atjaunināts"]);
} else {
    http_response_code(400);
    echo json_encode(["error"=>"Neizdevās atjaunināt"]);
}
$stmt->close();
}

elseif($method==='DELETE'){
    if(!isset($_GET['id'])){ http_response_code(400); echo json_encode(["error"=>"Nepieciešams ID"]); exit; }
    $id=(int)$_GET['id'];
    $stmt=$conn->prepare("DELETE FROM net_users WHERE id=? AND loma IN ('administrators','moderators')");
    $stmt->bind_param("i",$id);
    if($stmt->execute()) echo json_encode(["message"=>"Darbinieks dzēsts"]);
    else { http_response_code(400); echo json_encode(["error"=>"Neizdevās dzēst"]); }
    $stmt->close();
}
else{ http_response_code(405); echo json_encode(["error"=>"Metode nav atbalstīta"]); }

$conn->close();
?>
