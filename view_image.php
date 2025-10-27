<?php
session_start();
include("con_db.php");

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit("Missing ID");
}

$id = (int)$_GET['id'];
$stmt = $savienojums->prepare("SELECT bilde FROM net_reviews WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($bilde_path);
$stmt->fetch();
$stmt->close();

if (!$bilde_path || !file_exists($bilde_path)) {
    http_response_code(404);
    exit("File not found");
}

// Get MIME type from file
$mime = mime_content_type($bilde_path);
header("Content-Type: $mime");
header('Content-Disposition: inline; filename="' . basename($bilde_path) . '"');
readfile($bilde_path);
exit;
?>
