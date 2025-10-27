<?php
session_start();
include("../con_db.php");

$error = ""; // Initialize error variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($savienojums, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM net_users WHERE username='$username'";
    $result = mysqli_query($savienojums, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['loma'] = $row['loma']; // store role

            // Redirect based on role
            if ($row['loma'] === 'administrators' || $row['loma'] === 'moderators') {
                header("Location: ../admin/index.php");
                exit;
            } else {
                header("Location: ../index.php");
                exit;
            }
        } else {
            $error = "Nepareiza parole!";
        }
    } else {
        $error = "Lietotājs neeksistē!";
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../images/logo.png">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <title>Pieteikšanās</title>
    <link rel="stylesheet" href="../style.css?v=0.1">
    <script src="../script.js" defer></script>
</head>
<body class="auth-page">
    <div class="auth-container">
         <a href="../index.php" class="btn back-btn"><i class="fa fa-sign-out"></i></i></a>
        <h2>Pieteikšanās</h2>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

        <form method="POST" action="">
            <label>Lietotājvārds:</label>
            <input type="text" name="username" required>

            <label>Parole:</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn">Ieiet</button>
        </form>

        <p>Nav konta? <a href="register.php">Izveidot kontu</a></p>
    </div>
</body>
</html>
