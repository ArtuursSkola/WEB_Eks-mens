<?php
session_start();
require_once "con_db.php";

$response = ['success' => false, 'message' => ''];

$name = $_POST['name'] ?? '';
$review = $_POST['review'] ?? '';
$stars = (int)($_POST['stars'] ?? 0);
$bilde_path = null;

// Handle file upload
if (isset($_FILES['bilde']) && $_FILES['bilde']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/reviews/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $tmpName = $_FILES['bilde']['tmp_name'];
    $fileName = uniqid() . '-' . basename($_FILES['bilde']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($tmpName, $targetFile)) {
        $bilde_path = $targetFile;
    } else {
        $_SESSION['pazinojums_modal'] = "<p style='color:red;'>Kļūda augšupielādējot attēlu.</p>";
        header("Location: index.php");
        exit;
    }
}

$stmt = $savienojums->prepare("INSERT INTO net_reviews (name, review, stars, bilde, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssis", $name, $review, $stars, $bilde_path);

if ($stmt->execute()) {
    $_SESSION['pazinojums_modal'] = "<h2 style='color:green;'>Atsauksme ir veiksmīgi pievienota!</h2>";
} else {
    $_SESSION['pazinojums_modal'] = "<h2 style='color:red;'>Kļūda, mēģiniet vēlreiz.</h2>";
}

$stmt->close();
header("Location: index.php");
exit;
?>
