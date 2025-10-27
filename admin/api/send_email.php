<?php
session_start();
file_put_contents('debug_log.txt', "METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents('debug_log.txt', "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents('debug_log.txt', "RAW: " . file_get_contents("php://input") . "\n", FILE_APPEND);

// Always return JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

// Validate required fields
if (empty($_POST['email']) || empty($_POST['type'])) {
    echo json_encode(["error" => "Missing email or type"]);
    exit;
}

$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(["error" => "Invalid email address"]);
    exit;
}

$type = $_POST['type'];
$days_left = isset($_POST['days_left']) ? intval($_POST['days_left']) : null;

// Email headers
$from = "noreply@yourwebsite.com"; // Change to your domain email
$headers = "From: NetHelp <$from>\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Prepare email content
if ($type === "expiring") {
    if ($days_left === null) $days_left = "dažas"; // fallback if days_left missing
    $subject = "Jūsu abonements beigsies drīzumā";
    $message = "
        <h2>Labdien!</h2>
        <p>Jūsu abonements beigsies pēc <b>$days_left dienām</b>.</p>
        <p>Lūdzu, atjaunojiet savu abonementu, lai turpinātu izmantot mūsu pakalpojumus.</p>
        <p><a href='https://kristovskis.lv/4pt/kliecis/Net-Pakalpojumi/'>Atjaunot abonementu</a></p>
    ";
} elseif ($type === "expired") {
    $subject = "Jūsu abonements ir beidzies";
    $message = "
        <h2>Labdien!</h2>
        <p>Diemžēl jūsu abonements ir <b>beidzies</b>.</p>
        <p>Lai turpinātu izmantot mūsu pakalpojumus, lūdzu atjaunojiet savu abonementu.</p>
        <p>Jā izvēlaties mainīt plānu vai beigt sadarbību ar mums, tad atgrieziet mūsu isniegto rūteri 7 dienu laikā</p>
        <p>Ja rutētis netiks atgriezts 7 dienu laikā, tad tiks piemēros sods līdz pat 50 euro</p>
        <p><a href='https://kristovskis.lv/4pt/kliecis/Net-Pakalpojumi/'>Atjaunot abonementu</a></p>
    ";
} else {
    echo json_encode(["error" => "Invalid type"]);
    exit;
}

// Send email and return JSON result
if (mail($email, $subject, $message, $headers)) {
    echo json_encode(["success" => "E-pasts nosūtīts uz $email"]);
} else {
    echo json_encode(["error" => "E-pastu nevarēja nosūtīt"]);
}
