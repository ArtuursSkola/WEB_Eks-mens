<?php
session_start();
include("con_db.php");

$offers_per_page = 3;
$page = isset($_GET['offers_page']) ? max(1, intval($_GET['offers_page'])) : 1;
$offset = ($page - 1) * $offers_per_page;

$offers_query = mysqli_query($savienojums, "SELECT * FROM net_offers ORDER BY id ASC LIMIT $offset, $offers_per_page");
$offers = [];

while ($offer = mysqli_fetch_assoc($offers_query)) {
    $offers[] = $offer;
}

header('Content-Type: application/json');
echo json_encode(['offers' => $offers]);
