<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']); // To highlight active nav link
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interneta pakalpojumi</title>
    <link rel="shortcut icon" href="images/logo.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <script src="script.js" defer></script>
    <link rel="stylesheet" href="style.css?v=0.1">
</head>
<body>
    
<header>
    <a href="./" class="logo">
        <i class="fa fa-wifi" aria-hidden="true"></i>NetHelp
    </a>

<nav class="nav-menu">
    <!-- Desktop nav links -->
    <a href="#top" class="btn nav-link">Sākums</a>
    <a href="#plans" class="btn nav-link">Mūsu piedāvājumi</a>
    <a href="#about" class="btn nav-link">Par mums</a>
    <a href="#pakalpojumi"class="btn nav-link">Pakalpojumi</a>
    <a href="#reviews" class="btn nav-link">Atsauksmes</a>

    <?php if(isset($_SESSION['username'])): ?>
        <!-- User dropdown (only for logged in users) -->
        <div class="dropdown user-dropdown">
            <button class="btn" id="sveiki-btn">Sveiki, <?php echo htmlspecialchars($_SESSION['username']); ?></button>
            <div class="dropdown-content">
                <a href="#" id="edit-account-btn">Pārvaldīt kontu</a>
                <a href="login/logout.php">Iziet</a>
            </div>
        </div>
    <?php else: ?>
        <a href="login/login.php" class="btn login-btn">
            <i class="fa-solid fa-user"></i>
        </a>
    <?php endif; ?>

    <!-- Mobile menu dropdown (always visible for all users) -->
    <div class="dropdown menu-dropdown">
        <button class="btn" id="menu-btn"><i class="fa fa-bars"></i></button>
        <div class="dropdown-content">
            <a href="#top">Sākums</a>
            <a href="#plans">Mūsu piedāvājumi</a>
            <a href="#about">Par mums</a>
            <a href="#pakalpojumi">Pakalpojumi</a>
            <a href="#reviews">Atsauksmes</a>
        </div>
    </div>
</nav>

</header>
