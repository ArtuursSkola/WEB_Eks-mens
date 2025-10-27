<?php
include("../con_db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($savienojums, $_POST['username']);
    $vards = mysqli_real_escape_string($savienojums, $_POST['vards']);
    $uzvards = mysqli_real_escape_string($savienojums, $_POST['uzvards']);
    $email = mysqli_real_escape_string($savienojums, $_POST['email']);
    $telefons = mysqli_real_escape_string($savienojums, $_POST['telefons']);
    $password = $_POST['password'];

    // Validate phone number: must be exactly 8 digits
    if (!preg_match("/^\d{8}$/", $telefons)) {
        $error = "Telefona numuram jābūt precīzi 8 cipariem.";
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO net_users (username, vards, uzvards, email, telefons, password, loma) 
                VALUES ('$username', '$vards', '$uzvards', '$email', '$telefons', '$password_hashed', 'lietotajs')";

        if (mysqli_query($savienojums, $sql)) {
            header("Location: ../index.php");
            exit;
        } else {
            $error = "Kļūda: " . mysqli_error($savienojums);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reģistrācija</title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <link rel="shortcut icon" href="../images/logo.png">
    <link rel="stylesheet" href="../style.css?v=0.1">
    <script src="../script.js" defer></script>
</head>
<body class="auth-page">
    <div class="auth-container">
        <a href="../index.php" class="btn back-btn"><i class="fa fa-sign-out" aria-hidden="true"></i></a>

        <h2>Reģistrācija</h2>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

        <form method="POST" action="">
            <label>Lietotājvārds:</label>
            <input type="text" name="username" required>

            <label>Vārds:</label>
            <input type="text" name="vards" required>

            <label>Uzvārds:</label>
            <input type="text" name="uzvards" required>

            <label>E-pasts:</label>
            <input type="email" name="email" required>

            <label>Telefons:</label>
            <input type="text" name="telefons" maxlength="8" required>

            <label>Parole:</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn">Reģistrēties</button>
        </form>

        <p>Jau ir konts? <a href="login.php">Pieteikties</a></p>
    </div>
</body>
</html>
