<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']); 
$loma = $_SESSION['loma'] ?? ''; // current user's role
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetHelp Admin</title>
    <link rel="shortcut icon" href="../images/logo.png">
    <link rel="stylesheet" href="../style.css?v=0.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../script.js" defer></script>
</head>
<body>
<header>
    <a href="./index.php" class="logo">
        <i class="fa fa-wifi" aria-hidden="true"></i>NetHelp Admin
    </a>

    <nav class="admin-nav">
        <!-- Desktop nav links -->
        <a href="index.php" class="btn <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Sākums</a>
        <a href="klienti.php" class="btn <?php echo $current_page == 'klienti.php' ? 'active' : ''; ?>">Klienti</a>
        <a href="atsauksmes.php" class="btn <?php echo $current_page == 'atsauksmes.php' ? 'active' : ''; ?>">Atsauksmes</a>
         <a href="piedavajumi.php" class="btn <?php echo $current_page == 'piedavajumi.php' ? 'active' : ''; ?>">Pakalpojumi</a>
         <a href="plani.php" class="btn <?php echo $current_page == 'plani.php' ? 'active' : ''; ?>">Plāni</a>

        <?php if($loma === 'administrators'): ?>
            <a href="darbinieki.php" class="btn <?php echo $current_page == 'darbinieki.php' ? 'active' : ''; ?>">Darbinieki</a>
        <?php endif; ?>

        <a href="../login/logout.php" class="btn">Iziet</a>

        <!-- Mobile dropdown button -->
            <div class="dropdown mobile-dropdown">
        <button class="btn admin-menu-btn"><i class="fa fa-bars"></i></button>
        <div class="dropdown-content">
            <a href="index.php">Sākums</a>
            <a href="klienti.php">Klienti</a>
            <a href="atsauksmes.php">Atsauksmes</a>
            <a href="piedavajumi.php">Pakalpojumi</a>
            <a href="plani.php">Plāni</a>
            <?php if($loma === 'administrators'): ?>
                <a href="darbinieki.php">Darbinieki</a>
            <?php endif; ?>
            <a href="../login/logout.php">Iziet</a>
        </div>
    </div>

    </nav>
</header>
