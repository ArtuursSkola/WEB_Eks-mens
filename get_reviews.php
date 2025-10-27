<?php
require_once "con_db.php";

$result = mysqli_query($savienojums, "SELECT * FROM net_reviews ORDER BY created_at DESC LIMIT 3");
$reviews = [];

while ($row = mysqli_fetch_assoc($result)) {
    $reviews[] = [
        "name" => htmlspecialchars($row['name']),
        "review" => nl2br(htmlspecialchars($row['review'])),
        "stars" => (int)$row['stars']
    ];
}

header('Content-Type: application/json');
echo json_encode($reviews);
