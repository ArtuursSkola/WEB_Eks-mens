<?php
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$user = "grobina1_kliecis";
$pass = "gS8cBy218nhr@";
$db   = "grobina1_kliecis";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Neizdevās pieslēgties datubāzei"]); 
    exit;
}

session_start();
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
        $sql = $conn->prepare("SELECT * FROM net_reviews WHERE id=?");
        $sql->bind_param("i", $id);
        $sql->execute();
        $result = $sql->get_result();

        if ($row = $result->fetch_assoc()) {
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Atsauksme nav atrasta"]);
        }
        $sql->close();
    } else {
        $sql = "SELECT * FROM net_reviews ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode($rows);
    }
}

elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['name'], $data['review'], $data['stars'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešami visi dati"]);
        exit;
    }

    $sql = $conn->prepare("INSERT INTO net_reviews (name, review, stars, created_at) VALUES (?, ?, ?, NOW())");
    $sql->bind_param("ssi", $data['name'], $data['review'], $data['stars']);

    try {
        if ($sql->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Atsauksme pievienota", "id" => $sql->insert_id]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Neizdevās pievienot atsauksmi"]);
        }
    } catch (mysqli_sql_exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Kļūda: " . $e->getMessage()]);
    }

    $sql->close();
}


elseif ($method === 'PUT') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešams ID"]);
        exit;
    }

    $id = (int) $_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);

 $stmt = $conn->prepare("UPDATE net_reviews 
                        SET name=?, review=?, stars=?, last_edit_at=NOW() 
                        WHERE id=?");
$stmt->bind_param("ssii", $data['name'], $data['review'], $data['stars'], $id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Atsauksme atjaunota"]);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Neizdevās atjaunot"]);
}
$stmt->close();

}

elseif ($method === 'DELETE') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Nepieciešams ID"]);
        exit;
    }

    $id = (int) $_GET['id'];
    $sql = $conn->prepare("DELETE FROM net_reviews WHERE id=?");
    $sql->bind_param("i", $id);

    if ($sql->execute()) {
        echo json_encode(["message" => "Atsauksme dzēsta"]);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Neizdevās dzēst"]);
    }
    $sql->close();
}

else {
    http_response_code(405);
    echo json_encode(["error" => "Metode nav atbalstīta"]);
}

$conn->close();
?>
