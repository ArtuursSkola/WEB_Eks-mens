<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();
include("../../con_db.php");

$conn = mysqli_connect("localhost","grobina1_kliecis","gS8cBy218nhr@","grobina1_kliecis");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Neizdevās pieslēgties datubāzei"]);
    exit;
}

// Authorization
if (!isset($_SESSION['username']) || !isset($_SESSION['loma']) || 
    !in_array($_SESSION['loma'], ['administrators', 'moderators'])) {
    http_response_code(401);
    echo json_encode(["error" => "Nepieciešama autorizācija"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM net_plans WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) echo json_encode($row, JSON_UNESCAPED_UNICODE);
        else { http_response_code(404); echo json_encode(["error" => "Plāns nav atrasts"]); }
        $stmt->close();
    } else {
        $res = $conn->query("SELECT * FROM net_plans ORDER BY id ASC");
        $rows = [];
        while($row = $res->fetch_assoc()) $rows[] = $row;
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    }
}
// POST
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['name'], $data['speed'], $data['price'], $data['router_price'], $data['description'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešami dati"]);
        exit;
    }
    $icon = $data['icon'] ?? 'fas fa-tachometer-alt';
    $stmt = $conn->prepare("INSERT INTO net_plans (name, speed, price, router_price, description, icon, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssddss", $data['name'], $data['speed'], $data['price'], $data['router_price'], $data['description'], $icon);
    if ($stmt->execute()) echo json_encode(["message" => "Plāns izveidots"]);
    else { http_response_code(400); echo json_encode(["error" => "Neizdevās izveidot plānu"]); }
    $stmt->close();
}
// PUT
elseif ($method === 'PUT') {
    if (!isset($_GET['id'])) { http_response_code(400); echo json_encode(["error" => "Nepieciešams ID"]); exit; }
    $id = (int)$_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['name'], $data['speed'], $data['price'], $data['router_price'], $data['description'], $data['icon'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešami dati"]);
        exit;
    }
    $stmt = $conn->prepare("UPDATE net_plans SET name=?, speed=?, price=?, router_price=?, description=?, icon=?, last_edit_at=NOW() WHERE id=?");
    $stmt->bind_param("ssddssi", $data['name'], $data['speed'], $data['price'], $data['router_price'], $data['description'], $data['icon'], $id);
    if ($stmt->execute()) echo json_encode(["message" => "Plāns atjaunināts"]);
    else { http_response_code(400); echo json_encode(["error" => "Neizdevās atjaunināt plānu"]); }
    $stmt->close();
}
// DELETE
elseif ($method === 'DELETE') {
    if(!isset($_GET['id'])) { http_response_code(400); echo json_encode(["error"=>"Nepieciešams ID"]); exit; }
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM net_plans WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) echo json_encode(["message" => "Plāns dzēsts"]);
    else { http_response_code(400); echo json_encode(["error" => "Neizdevās dzēst plānu"]); }
    $stmt->close();
}
else {
    http_response_code(405);
    echo json_encode(["error" => "Metode nav atbalstīta"]);
}
$conn->close();
?>
