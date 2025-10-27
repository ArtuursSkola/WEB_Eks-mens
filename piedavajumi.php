
<?php
$current_page = basename($_SERVER['PHP_SELF']);

session_start();
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Piedāvājumi</title>
    <link rel="shortcut icon" href="images/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=0.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    <script src="script.js" defer></script>

</head>
<body>
<header>
    <a href="./index.php" class="logo">
        <i class="fa fa-wifi" aria-hidden="true"></i>NetHelp Admin
    </a>
<nav>
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
<div class="piedavajumi-kaste">
<main class="admin-container">
    <h1>Mūsu interneta plāni un piedāvājumi</h1>

    <!-- Ekonomiskais plāns -->
<!-- Ekonomiskais plāns -->
<section class="plan-box">
    <h2>Ekonomiskais plāns</h2>
    <p><strong>Ātrums:</strong> 10 Mb/s (lejupielāde), 2 Mb/s (augšupielāde)</p>
    <p><strong>Cena:</strong> 9.99 €/mēn</p>
    <p><strong>Datu limits:</strong> 200 GB mēnesī</p>
    <p><strong>Atbalstītās ierīces:</strong> līdz 3 vienlaicīgas ierīces</p>
    <p><strong>Streaming kvalitāte:</strong> SD video straumēšana (480p)</p>
    <p><strong>Uzticamība:</strong> Stabils internets ikdienas lietošanai, piemērots e-pastiem, pārlūkošanai un vieglām interneta darbībām.</p>
    <p>Šis plāns ir ideāls, ja jūsu galvenais mērķis ir ietaupīt naudu. Tas nodrošina pietiekamu savienojumu, lai ikdienas darbības tiktu veiktas bez pārtraukumiem, bet nav piemērots lielas slodzes straumēšanai vai failu lejupielādei.</p>
    <?php if(isset($_SESSION['username'])): ?>
        <a href="payments/checkout.php?plan=Ātrais&price=1999" class="btn" id="cards-btn">Iegādāties</a>
    <?php else: ?>
        <a href="login/login.php" class="btn" id="cards-btn">Iegādāties</a>
    <?php endif; ?>
</section>

<!-- Normālais plāns -->
<section class="plan-box">
    <h2>Normālais plāns</h2>
    <p><strong>Ātrums:</strong> 50 Mb/s (lejupielāde), 10 Mb/s (augšupielāde)</p>
    <p><strong>Cena:</strong> 14.99 €/mēn</p>
    <p><strong>Datu limits:</strong> 500 GB mēnesī</p>
    <p><strong>Atbalstītās ierīces:</strong> līdz 6 vienlaicīgas ierīces</p>
    <p><strong>Streaming kvalitāte:</strong> HD video straumēšana (1080p)</p>
    <p><strong>Uzticamība:</strong> Lieliska veiktspēja ģimenēm vai mājas birojiem. Nodrošina stabilu internetu vairākiem lietotājiem vienlaikus.</p>
    <p>Normālais plāns piedāvā optimālu ātrumu un cenu kombināciju, kas ļauj straumēt HD video, spēlēt tiešsaistes spēles un veikt videokonferences bez aizkavēm. Tas ir ideāli piemērots lietotājiem, kuri vēlas kvalitatīvu interneta pieredzi par pieņemamu cenu.</p>
    <?php if(isset($_SESSION['username'])): ?>
        <a href="payments/checkout.php?plan=Ātrais&price=1999" class="btn" id="cards-btn">Iegādāties</a>
    <?php else: ?>
        <a href="login/login.php" class="btn" id="cards-btn">Iegādāties</a>
    <?php endif; ?>
</section>

<!-- Ātrais plāns -->
<section class="plan-box">
    <h2>Ātrais plāns</h2>
    <p><strong>Ātrums:</strong> 100 Mb/s (lejupielāde), 20 Mb/s (augšupielāde)</p>
    <p><strong>Cena:</strong> 19.99 €/mēn</p>
    <p><strong>Datu limits:</strong> Neierobežots</p>
    <p><strong>Atbalstītās ierīces:</strong> līdz 10 vienlaicīgas ierīces</p>
    <p><strong>Streaming kvalitāte:</strong> 4K un UHD video straumēšana</p>
    <p><strong>Uzticamība:</strong> Maksimāla veiktspēja, piemērots profesionāļiem un lielām ģimenēm. Nodrošina stabilu un ātru interneta savienojumu visām ierīcēm.</p>
    <p>Ātrais plāns ir vispiemērotākais, ja nepieciešama maksimāla ātruma interneta pieredze. Tas ļauj straumēt 4K video, lejupielādēt lielus failus, spēlēt tiešsaistes spēles ar zemu latentumu un nodrošina nevainojamu darbību vairākām ierīcēm vienlaikus.</p>
    <?php if(isset($_SESSION['username'])): ?>
        <a href="payments/checkout.php?plan=Ātrais&price=1999" class="btn" id="cards-btn">Iegādāties</a>
    <?php else: ?>
        <a href="login/login.php" class="btn" id="cards-btn">Iegādāties</a>
    <?php endif; ?>
</section>


    <h2>Salīdzinājuma tabula</h2>
    <p>Tabulā zemāk jūs varat viegli salīdzināt mūsu trīs populārākos plānus un izvēlēties piemērotāko atbilstoši jūsu vajadzībām.</p>

    <table>
        <thead>
            <tr>
                <th>Plāns</th>
                <th>Ātrums</th>
                <th>Cena</th>
                <th>Ideāli piemērots</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ekonomiskais plāns</td>
                <td>10 Mb/s</td>
                <td>9.99 €/mēn</td>
                <td>Lietotājiem, kuri vēlas ietaupīt naudu un veikt pamata interneta darbības</td>
            </tr>
            <tr>
                <td>Normālais plāns</td>
                <td>50 Mb/s</td>
                <td>14.99 €/mēn</td>
                <td>Ģimenēm vai mājas birojiem ar ikdienas interneta lietošanas vajadzībām</td>
            </tr>
            <tr>
                <td>Ātrais plāns</td>
                <td>100 Mb/s</td>
                <td>19.99 €/mēn</td>
                <td>Lietotājiem, kuriem nepieciešams augstākais ātrums un veiktspēja</td>
            </tr>
        </tbody>
    </table>

    <p>Izvēloties plānu, ieteicams novērtēt jūsu interneta lietošanas paradumus, ierīču skaitu un nepieciešamo ātrumu. Ja vēlaties ietaupīt, izvēlieties Ekonomisko plānu. Ja vēlaties līdzsvarotu ātrumu un cenu, izvēlieties Normālo plānu. Ja ātrums ir vissvarīgākais, izvēlieties Ātro plānu.</p>
</main>
</div>
<footer id="contact">
    <div class="footer-content">
        <p><i class="fas fa-map-marker-alt"></i> Liepāja, Latvija</p>
        <p><i class="fas fa-phone"></i> +371 20000000</p>
        <p><i class="fas fa-envelope"></i> info@nethelp.lv</p>
    </div>
    <button id="dark-mode-toggle" class="btn">🌙 Tumšais režīms</button>
    <p class="copyright">© 2025 NetHelp. Visas tiesības aizsargātas.</p>
</footer>
<style>
.admin-container { padding: 2rem; }
.plan-box { border: 1px solid #ccc; padding: 1.5rem; margin-bottom: 2rem; border-radius: 8px; background-color: #e5e1e1ff; }
.plan-box h2 { color: #05b823; margin-bottom: 0.5rem; }
.plan-box p { margin-bottom: 0.5rem; }
table { width: 100%; border-collapse: collapse; margin-top: 2rem; margin-bottom: 2rem; }
th, td { border: 1px solid #ccc; padding: 0.75rem 1rem; text-align: left; }
th { background-color: #05b823; color: #fff; }
td { background-color: #e5e1e1ff; }
button.btn { padding: 0.5rem 1rem; background-color: #05b823; color: #fff; border-radius: 5px; border: none; cursor: pointer; }
button.btn:hover { background-color: #047a15; }
</style>



</body>
</html>
