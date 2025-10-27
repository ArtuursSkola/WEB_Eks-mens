<?php
session_start();
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Par mums</title>
    <link rel="shortcut icon" href="images/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=0.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <script src="script.js" defer></script>

</head>
<body>
<header>
    <a href="./index.php" class="logo">
        <i class="fa fa-wifi" aria-hidden="true"></i>NetHelp
    </a>
    <nav>
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <a href="./" class="btn <?php echo ($current_page == 'index.php' || $current_page == '') ? 'active' : ''; ?>">Sākums</a>
        <a href="piedavajumi.php" class="btn <?php echo ($current_page == 'piedavajumi.php') ? 'active' : ''; ?>">Mūsu piedāvājumi</a>
        <a href="about.php" class="btn <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">Par mums</a>

        <?php if(isset($_SESSION['username'])): ?>
        <div class="dropdown">
            <button class="btn" id="sveiki-btn">Sveiki <?php echo htmlspecialchars($_SESSION['username']); ?>!</button>
            <div class="dropdown-content">
                <a href="#" id="edit-account-btn">Pārvaldīt kontu</a>
                <a href="login/logout.php">Iziet</a>
            </div>
        </div>
        <?php else: ?>
        <a href="login/login.php" class="btn"><i class="fa-solid fa-user"></i></a>
        <?php endif; ?>
    </nav>
</header>

<main class="about-page">
    <div class="piedavajumi-kaste">
    <section class="about" id="par-mums">
        <h1>Par mums</h1>
        <div class="about-container">
            <div class="about-text">
                <p>
                    <strong>NetHelp</strong> ir Latvijas interneta pakalpojumu sniedzējs, kas 
                    jau vairāk nekā desmit gadus rūpējas par saviem klientiem. Mēs lepojamies ar 
                    to, ka spējam apvienot <strong>augstu kvalitāti</strong>, <strong>uzticamību</strong> un 
                    <strong>pieejamu cenu</strong> vienuviet.
                </p>
                <p>
                    Mūsu komanda sastāv no pieredzējušiem speciālistiem telekomunikāciju un 
                    klientu apkalpošanas jomā. Mēs ticam, ka mūsdienu pasaulē stabils internets nav 
                    tikai ērtība – tas ir pamats darbam, mācībām, izklaidei un saziņai ar tuvajiem.
                </p>
                <p>
                    Mūsu mērķis ir nodrošināt, lai <strong>ikvienam klientam</strong> būtu pieejams 
                    ātrs un drošs internets neatkarīgi no dzīvesvietas – gan lielpilsētās, gan 
                    lauku reģionos. Tieši tāpēc mēs nepārtraukti paplašinām savu pārklājumu un 
                    investējam jaunākajās tehnoloģijās.
                </p>
                <p>
                    <strong>Mūsu vērtības:</strong>
                </p>
                <ul>
                    <ol><i class="fas fa-bolt"></i> Inovācijas – vienmēr sekojam līdzi jaunākajiem risinājumiem.</ol>
                    <ol><i class="fas fa-users"></i> Klientu apmierinātība – mūsu prioritāte ir jūsu vajadzības.</ol>
                    <ol><i class="fas fa-shield-alt"></i> Drošība – rūpējamies par datu aizsardzību un stabilitāti.</ol>
                    <ol><i class="fas fa-handshake"></i> Uzticamība – solām tikai to, ko patiešām varam nodrošināt.</ol>
                </ul>
                <p>
                    Paldies, ka izvēlaties <strong>NetHelp</strong>. Mēs esam šeit, lai 
                    palīdzētu jums būt savienotiem ar visu, kas svarīgs!
                </p>
            </div>
        </div>
    </section>
        </div>
</main>

<footer id="contact">
    <div class="footer-content">
        <p><i class="fas fa-map-marker-alt"></i> Liepāja, Latvija</p>
        <p><i class="fas fa-phone"></i> +371 20000000</p>
        <p><i class="fas fa-envelope"></i> info@nethelp.lv</p>
    </div>
    <button id="dark-mode-toggle" class="btn">🌙 Tumšais režīms</button>
    <p class="copyright">© 2025 NetHelp. Visas tiesības aizsargātas.</p>
</footer>

</body>
</html>
