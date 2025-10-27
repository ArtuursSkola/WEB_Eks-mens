<?php
header("Content-Type: application/json; charset=UTF-8");

session_start();
include("../../con_db.php");

$host = "localhost";
$user = "grobina1_kliecis";
$pass = "gS8cBy218nhr@";
$db   = "grobina1_kliecis";

$conn = mysqli_connect($host, $user, $pass, $db);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Neizdevās pieslēgties datubāzei"]); 
    exit;
}

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error"=>"DB connect failed"]);
    exit;
}



if (!isset($_SESSION['username']) || !isset($_SESSION['loma']) || 
    !in_array($_SESSION['loma'], ['administrators', 'moderators'])) {
    
    http_response_code(401); 
    echo json_encode(["error" => "Nepieciešama autorizācija"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $sql = $conn->prepare("SELECT * FROM net_users WHERE id=?");
        $sql->bind_param("i", $id);
        $sql->execute();
        $result = $sql->get_result();

        if ($row = $result->fetch_assoc()) {
            echo json_encode($row, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Lietotājs nav atrasts"], JSON_UNESCAPED_UNICODE);
        }
        $sql->close();
    } else {
        $sql = "SELECT * FROM net_users WHERE loma='lietotajs' ORDER BY id DESC";
        $result = $conn->query($sql);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    }
}

// Example PUT handler for offers
else if ($method === 'PUT') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešams ID"]);
        exit;
    }

    $id = (int) $_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate required fields for users
    if (!isset($data['username'], $data['vards'], $data['uzvards'], $data['email'], $data['plan'], $data['telefons'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešami dati"]);
        exit;
    }

    // If plan is changed from 'Nav', update plan_purchased_at if necessary
    $planPurchasedAt = ($data['plan'] !== 'Nav') ? date('Y-m-d H:i:s') : null;

        $sql = $conn->prepare("UPDATE net_users 
            SET username=?, vards=?, uzvards=?, email=?, telefons=?, plan=?, plan_purchased_at=?, last_edit_at=NOW() 
            WHERE id=?");

        $sql->bind_param(
            "sssssssi",
            $data['username'],
            $data['vards'],
            $data['uzvards'],
            $data['email'],
            $data['telefons'],
            $data['plan'],
            $planPurchasedAt,
            $id
        );


    if ($sql->execute()) {
        echo json_encode(["message" => "Lietotājs atjaunināts"], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Neizdevās atjaunināt lietotāju"], JSON_UNESCAPED_UNICODE);
    }

    $sql->close();
    exit;
}


elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate required fields
    if (!isset($data['username'], $data['vards'], $data['uzvards'], $data['email'], $data['loma'], $data['password'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešami dati"]);
        exit;
    }

    // Hash password
    $password_hashed = password_hash($data['password'], PASSWORD_DEFAULT);

    // Optional fields
    $telefons = $data['telefons'] ?? '';
    $plan = (!empty($data['plan'])) ? $data['plan'] : 'Nav';

    // Set plan_purchased_at only if plan is not 'Nav'
    $planPurchasedAt = ($plan !== 'Nav') ? date('Y-m-d H:i:s') : null;

    // Prepare SQL
    $sql = $conn->prepare(
        "INSERT INTO net_users (username, vards, uzvards, email, telefons, plan, plan_purchased_at, loma, password, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    $sql->bind_param(
        "sssssssss",
        $data['username'],
        $data['vards'],
        $data['uzvards'],
        $data['email'],
        $telefons,
        $plan,
        $planPurchasedAt,
        $data['loma'],
        $password_hashed
    );

    // Execute and handle errors
    try {
        if ($sql->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Lietotājs izveidots"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Neizdevās izveidot lietotāju"]);
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // duplicate entry
            http_response_code(400);
            echo json_encode(["error" => "Šāds lietotājvārds jau eksistē!"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Kļūda: " . $e->getMessage()]);
        }
    }

    $sql->close();
    exit;
}






elseif ($method === 'DELETE') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešams ID"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $id = (int) $_GET['id'];
    $sql = $conn->prepare("DELETE FROM net_users WHERE id=?");
    $sql->bind_param("i", $id);

    if ($sql->execute()) {
        echo json_encode(["message" => "Lietotājs dzēsts"], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Neizdevās dzēst"], JSON_UNESCAPED_UNICODE);
    }
    $sql->close();
}

else {
    http_response_code(405);
    echo json_encode(["error" => "Metode nav atbalstīta"], JSON_UNESCAPED_UNICODE);
}

$conn->close();

?>
