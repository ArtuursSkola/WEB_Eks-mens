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
        $sql = $conn->prepare("SELECT * FROM net_offers WHERE id=?");
        $sql->bind_param("i", $id);
        $sql->execute();
        $result = $sql->get_result();
        if ($row = $result->fetch_assoc()) echo json_encode($row, JSON_UNESCAPED_UNICODE);
        else { http_response_code(404); echo json_encode(["error" => "Piedāvājums nav atrasts"]); }
        $sql->close();
    } else {
        $sql = "SELECT * FROM net_offers ORDER BY id DESC";
        $result = $conn->query($sql);
        $rows = [];
        while($row = $result->fetch_assoc()) $rows[] = $row;
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    }
}

// POST - Create
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['name'], $data['description'], $data['price'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešami dati"]);
        exit;
    }
    $icon = $data['icon'] ?? '';
    $orders = $data['orders'] ?? 0;

    $sql = $conn->prepare("INSERT INTO net_offers (name, description, price, icon, orders, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$sql->bind_param("ssdsi", $data['name'], $data['description'], $data['price'], $icon, $orders);


    if($sql->execute()) echo json_encode(["message"=>"Piedāvājums izveidots"]);
    else { http_response_code(400); echo json_encode(["error"=>"Neizdevās izveidot"]); }
    $sql->close();
}

// PUT - Edit
// Example PUT handler for offers
elseif ($method === 'PUT') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešams ID"]);
        exit;
    }

    $id = (int) $_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate required fields
    if (!isset($data['name'], $data['description'], $data['price'], $data['icon'], $data['orders'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešami dati"]);
        exit;
    }

$sql = $conn->prepare("UPDATE net_offers SET name=?, description=?, price=?, icon=?, orders=?, last_edit_at=NOW() WHERE id=?");
$sql->bind_param("ssdssi", $data['name'], $data['description'], $data['price'], $data['icon'], $data['orders'], $id);


    if ($sql->execute()) {
        echo json_encode(["message" => "Piedāvājums atjaunināts"]);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Neizdevās atjaunināt piedāvājumu"]);
    }
    $sql->close();
    exit;
}


// DELETE
elseif ($method === 'DELETE') {
    if(!isset($_GET['id'])) { http_response_code(400); echo json_encode(["error"=>"Nepieciešams ID"]); exit; }
    $id = (int) $_GET['id'];
    $sql = $conn->prepare("DELETE FROM net_offers WHERE id=?");
    $sql->bind_param("i", $id);
    if($sql->execute()) echo json_encode(["message"=>"Piedāvājums dzēsts"]);
    else { http_response_code(400); echo json_encode(["error"=>"Neizdevās dzēst"]); }
    $sql->close();
}
else {
    http_response_code(405);
    echo json_encode(["error"=>"Metode nav atbalstīta"]);
}

$conn->close();
?>
