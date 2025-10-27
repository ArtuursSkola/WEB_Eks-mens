<?php
session_start();
include("con_db.php");

$reviews_sql = "SELECT * FROM net_reviews ORDER BY created_at DESC LIMIT 3";
$reviews_result = mysqli_query($savienojums, $reviews_sql);
$current_page = basename($_SERVER['PHP_SELF']);

$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$plan = 'Nav';
$days_left = 0;

if ($username) {
    $stmt = $savienojums->prepare("SELECT plan, plan_purchased_at FROM net_users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $plan = $row['plan'] ?? 'Nav';
        if ($row['plan_purchased_at'] && $plan !== 'Nav') {
            $expiry = new DateTime($row['plan_purchased_at']);
            $now = new DateTime();
            $days_left = ($expiry > $now) ? $now->diff($expiry)->days : 0;
        }
    }
    $stmt->close();
}

$plans_per_page = 3;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $plans_per_page;
include("header.php");

$total_plans_result = mysqli_query($savienojums, "SELECT COUNT(*) as total FROM net_plans");
$total_plans = mysqli_fetch_assoc($total_plans_result)['total'] ?? 0;
$total_pages = ceil($total_plans / $plans_per_page);

$plans_query = mysqli_query($savienojums, "SELECT * FROM net_plans ORDER BY id ASC LIMIT $offset, $plans_per_page");

// Define class names so your CSS layout stays the same
$plan_classes = ['pirmais', 'otrais', 'tresais'];
$index = 0;

$offers_per_page = 3;
$page = isset($_GET['offers_page']) ? max(1, intval($_GET['offers_page'])) : 1;
$offset = ($page - 1) * $offers_per_page;

// Count total offers
$total_offers_result = mysqli_query($savienojums, "SELECT COUNT(*) as total FROM net_offers");
$total_offers = mysqli_fetch_assoc($total_offers_result)['total'] ?? 0;
$total_offer_pages = ceil($total_offers / $offers_per_page);

// Get offers for current page
$offers_query = mysqli_query($savienojums, "SELECT * FROM net_offers ORDER BY id ASC LIMIT $offset, $offers_per_page");

?>

<div id="top"></div>


<div id="plans" class="main">
    <div class="top-part">
        <h1>interneta pakalpojumu sniedzējs <i class="fa fa-wifi" aria-hidden="true"></i><span class="nosaukums">NetHelp</span></h1>
        <div class="piedavajumi">
            <?php if(mysqli_num_rows($plans_query) > 0): ?>
                <?php while ($plan_data = mysqli_fetch_assoc($plans_query)): 
                    $class_name = $plan_classes[$index % count($plan_classes)];
                    $plan_name = htmlspecialchars($plan_data['name']);
                    $speed = htmlspecialchars($plan_data['speed']);
                    $price = number_format($plan_data['price'], 2);
                    $router = number_format($plan_data['router_price'], 2);
                    $description = htmlspecialchars($plan_data['description']);
                    $icon = !empty($plan_data['icon']) ? $plan_data['icon'] : "fas fa-tachometer-alt";
                    $checkout_price = ($plan_data['price'] * 100) + ($plan_data['router_price'] * 100); // cents
                ?>
                <div class="<?= $class_name ?>">
                    <h2><?= $plan_name ?></h2>
                    <div class="top-card">
                        <p><i class="<?= $icon ?>"></i> Ātrums: <?= $speed ?></p>
                        <p><i class='fas fa-euro-sign'></i>Cena: <?= $price ?> €/mēn</p>
                        <p><i class="fa-solid fa-wifi"></i>Rūtera īre: <?= $router ?> €/mēn</p>
                    </div>
                    <div class="middle-card">
                        <p><?= $description ?></p>
                    </div>
                    <?php if(isset($_SESSION['username'])): ?>
                        <a href="payments/checkout.php?plan=<?= urlencode($plan_name) ?>&price=<?= $checkout_price ?>" class="btn" id="cards-btn">Iegādāties</a>
                    <?php else: ?>
                        <a href="login/login.php" class="btn" id="cards-btn">Iegādāties</a>
                    <?php endif; ?>
                </div>
                <?php 
                    $index++;
                endwhile; ?>
            <?php else: ?>
                <p>Pašlaik nav pieejamu plānu.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?page=<?= $page-1 ?>" class="prev">&laquo; Iepriekšējā</a>
                <?php endif; ?>

                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?>" class="next">Nākamā &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="uzzinat" id="uzzinat">
            <button class="btn" id="openPiedavajumiModal">Uzzināt vairāk</button>
        </div>
    </div>
</div>



<div id="editAccountModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Pārvaldīt kontu</h2>

        <!-- Display current plan -->
        <p><strong>Jūsu plāns:</strong> <?php echo htmlspecialchars($plan); ?></p>
        <p><strong>Dienas līdz termiņa beigām:</strong> <?php echo $days_left+1; ?> dienas</p>

        <form method="POST" id="editAccountForm">
            <label>Mainīt Lietotājvārdu vai paroli</label>
            <label>Lietotājvārds:</label>
            <input type="text" name="new_username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>

            <label>Parole:</label>
            <input type="password" name="new_password" placeholder="Jauna parole">

            <button type="submit" class="btn">Saglabāt</button>
        </form>
        <p id="editAccountMsg" style="color:green;"></p>
    </div>
</div>




<section id="about" class="about">
    <h2>Ko mēs piedāvājam?</h2>
    <p>Mēs piedāvājam drošus un ātrus interneta pakalpojumus visā Latvijā. 
       Mūsu mērķis ir nodrošināt klientiem kvalitatīvu savienojumu par pieejamu cenu.</p>

    <div class="features">
        <div class="feature-box">
            <i class="fas fa-bolt fa-2x"></i>
            <h3>Ātrs internets</h3>
            <p>Vienmērīgs savienojums gan mājās, gan darbā.</p>
        </div>
        <div class="feature-box">
            <i class="fas fa-shield-alt fa-2x"></i>
            <h3>Drošs savienojums</h3>
            <p>Stabilitāte un drošība jūsu datiem.</p>
        </div>
        <div class="feature-box">
            <i class="fas fa-headset fa-2x"></i>
            <h3>Atbalsts 24/7</h3>
            <p>Mūsu speciālisti vienmēr gatavi palīdzēt.</p>
        </div>
        <div class="feature-box">
            <i class="fas fa-euro-sign fa-2x"></i>
            <h3>Izdevīgi tarifi</h3>
            <p>Elastīgi plāni katra klienta vajadzībām.</p>
        </div>
    </div>
    <div class="uzzinat">
    <button class="btn" id="openAboutModal">Uzzināt vairāk</button>
    </div>
</section>

<!-- Pakalpojumi Section -->

<!-- Pakalpojumi Section -->
<section id="pakalpojumi" class="pakalpojumi">
    <h2>Pakalpojumi</h2>
    <p>Mēs ne tikai piedāvājam interneta abonementus, bet arī pakalpojumus, kas jums palīdzēs ērtāt izmantot mūsu internetu. Mūsu galvenais mērķis ir, lai mūsu klienti būtu apmierināti.</p>

    <div class="pakalpojumi-container" id="offersContainer">
        <?php while($offer = mysqli_fetch_assoc($offers_query)): ?>
        <div class="pakalpojums-card">
            <div class="pakalpojums-icon"><i class="<?= $offer['icon'] ?>"></i></div>
            <h3><?= htmlspecialchars($offer['name']) ?></h3>
            <p><?= htmlspecialchars($offer['description']) ?></p>
            <div class="pakalpojums-footer">
                <p class="price">Cena: <?= number_format($offer['price'],2) ?> €</p>
                <button class="btn order-btn" onclick="window.location='payments/checkout_offer.php?offer_id=<?= $offer['id'] ?>'">Pasūtīt</button>
                <p class="order-count">Pasūtījumi: <?= $offer['orders'] ?></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination Controls -->
<?php if ($total_offer_pages > 1): ?>
    <div class="pagination" id="offersPagination">
        <button class="btn" id="prevOfferBtn">&laquo; Iepriekšējā</button>
        <button class="btn" id="nextOfferBtn">Nākamā &raquo;</button>
    </div>
<?php endif; ?>


</section>




<section id="reviews" class="reviews">
    <h2>Atsauksmes no klientiem</h2>
    <p>Atsauksmes kuras klienti ir snieguši par mūsu interneta pakalpojumu, vai mūsu citiem pakalpojumiem</p>
    <div class="reviews-container">
        <?php if(mysqli_num_rows($reviews_result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($reviews_result)): ?>
                <div class="review-box">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($row['review'])); ?></p>
                    <p>
                        <?php
                        for($i = 1; $i <= 5; $i++) {
                            echo $i <= $row['stars'] ? "★" : "☆";
                        }
                        ?>
                    </p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nav vēl atsauksmju.</p>
        <?php endif; ?>
    </div>

<button class="btn" id="openReviewModalBtn">Pievienot atsauksmi</button>

    <!-- Submit review form -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Pievienot atsauksmi</h3>    
                <form id="reviewForm" method="POST" action="submit_review.php" enctype="multipart/form-data">
                    <label>Jūsu vārds:</label>
                    <input type="text" name="name" required>

                    <label>Atsauksme:</label>
                    <textarea name="review" required></textarea>

                    <label>Novērtējums (1-5 zvaigznes):</label>
                    <select name="stars" required>
                        <option value="1">1 ★</option>
                        <option value="2">2 ★★</option>
                        <option value="3">3 ★★★</option>
                        <option value="4">4 ★★★★</option>
                        <option value="5">5 ★★★★★</option>
                    </select>

                    <label>Augšupielādēt attēlu (pēc izvēles):</label>
                    <input type="file" name="bilde" accept="image/*">

                    <button type="submit" class="btn">Iesniegt</button>
                </form>

        </div>
    </div>
</section>
<?php if(isset($_SESSION["pazinojums_modal"])): ?>
<div id="successModal" class="modal" style="display:flex;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <?php 
            echo $_SESSION["pazinojums_modal"]; 
            unset($_SESSION["pazinojums_modal"]);
        ?>
    </div>
</div>

<script>
const modal = document.getElementById('successModal');
const closeBtn = modal.querySelector('.close');
closeBtn.onclick = () => modal.style.display = 'none';
window.onclick = e => { if(e.target === modal) modal.style.display = 'none'; };
</script>
<?php endif; ?>


<?php
// Include footer
include("footer.php");
?>

<!-- Piedavajumi Modal -->
<div id="piedavajumiModal" class="modal">
    <div class="modal-content modal-large">
        <span class="close">&times;</span>
        <h2>Mūsu interneta plāni un piedāvājumi</h2>

        <!-- Paste the piedavajumi.php MAIN CONTENT only (without <html>, <head>, <body>) -->
        <section class="plan-box">
            <h2>Ekonomiskais plāns</h2>
            <p><strong>Ātrums:</strong> 10 Mb/s (lejupielāde), 2 Mb/s (augšupielāde)</p>
            <p><strong>Cena:</strong> 9.99 €/mēn</p>
            <p><strong>Datu limits:</strong> 200 GB mēnesī</p>
            <p><strong>Atbalstītās ierīces:</strong> līdz 3 vienlaicīgas ierīces</p>
            <p><strong>Streaming kvalitāte:</strong> SD video straumēšana (480p)</p>
            <p><strong>Uzticamība:</strong> Stabils internets ikdienas lietošanai.</p>
        </section>

        <section class="plan-box">
            <h2>Normālais plāns</h2>
            <p><strong>Ātrums:</strong> 50 Mb/s (lejupielāde), 10 Mb/s (augšupielāde)</p>
            <p><strong>Cena:</strong> 14.99 €/mēn</p>
            <p><strong>Datu limits:</strong> 500 GB mēnesī</p>
            <p><strong>Atbalstītās ierīces:</strong> līdz 6 vienlaicīgas ierīces</p>
            <p><strong>Streaming kvalitāte:</strong> HD video straumēšana (1080p)</p>
        </section>

        <section class="plan-box">
            <h2>Ātrais plāns</h2>
            <p><strong>Ātrums:</strong> 100 Mb/s (lejupielāde), 20 Mb/s (augšupielāde)</p>
            <p><strong>Cena:</strong> 19.99 €/mēn</p>
            <p><strong>Datu limits:</strong> Neierobežots</p>
            <p><strong>Atbalstītās ierīces:</strong> līdz 10 vienlaicīgas ierīces</p>
            <p><strong>Streaming kvalitāte:</strong> 4K video straumēšana</p>
        </section>

        <h2>Salīdzinājuma tabula</h2>
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
                    <td>Ekonomiskais</td>
                    <td>10 Mb/s</td>
                    <td>9.99 €/mēn</td>
                    <td>Pamata lietošanai</td>
                </tr>
                <tr>
                    <td>Normālais</td>
                    <td>50 Mb/s</td>
                    <td>14.99 €/mēn</td>
                    <td>Ģimenēm / mājas birojiem</td>
                </tr>
                <tr>
                    <td>Ātrais</td>
                    <td>100 Mb/s</td>
                    <td>19.99 €/mēn</td>
                    <td>Intensīvai lietošanai</td>
                </tr>
            </tbody>
        </table>
        <!-- Image Gallery Section -->
          <h2>Interneta plāna rūteri</h2>
            <div class="plan-gallery">
               
                <div class="plan-img-box">
                    <img src="images/first.jpg" alt="Ekonomiskais plāns rūteris">
                    <p>Ekonomiskā plāna rūteris</p>
                </div>
                <div class="plan-img-box">
                    <img src="images/second.jpg" alt="Normālais plāns rūteris">
                    <p>Normālā plāna rūteris</p>
                </div>
                <div class="plan-img-box">
                    <img src="images/third.jpg" alt="Ātrais plāns rūteris">
                    <p>Ātrā plāna rūteris</p>
                </div>
            </div>

    </div>
</div>

<!-- About Modal -->
<div id="aboutModal" class="modal">
    <div class="modal-content about-modal">
        <span class="close">&times;</span>

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

                <h2>Mūsu vērtības</h2>
                <ul class="about-values">
                    <li><i class="fas fa-bolt"></i> Inovācijas – vienmēr sekojam līdzi jaunākajiem risinājumiem.</li>
                    <li><i class="fas fa-users"></i> Klientu apmierinātība – mūsu prioritāte ir jūsu vajadzības.</li>
                    <li><i class="fas fa-shield-alt"></i> Drošība – rūpējamies par datu aizsardzību un stabilitāti.</li>
                    <li><i class="fas fa-handshake"></i> Uzticamība – solām tikai to, ko patiešām varam nodrošināt.</li>
                </ul>

                <h2>Mūsu misija</h2>
                <p>
                    Mēs cenšamies sniegt visiem klientiem kvalitatīvu interneta pieredzi ar moderniem risinājumiem, 
                    draudzīgu klientu atbalstu un pieejamām cenām. <strong>NetHelp</strong> – jūsu uzticamais interneta partneris.
                </p>

                <p>
                    Paldies, ka izvēlaties <strong>NetHelp</strong>. Mēs esam šeit, lai 
                    palīdzētu jums būt savienotiem ar visu, kas svarīgs!
                </p>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const openBtn = document.getElementById('openReviewModalBtn');
    const modal = document.getElementById('reviewModal');
    const closeBtn = modal.querySelector('.close');

    // Open modal
    openBtn?.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    // Close when clicking ×
    closeBtn?.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Close when clicking outside modal
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-link');

    function setActiveNav() {
        const scrollPos = window.scrollY;
        let activeFound = false;

        navLinks.forEach(link => link.classList.remove('active'));

        navLinks.forEach(link => {
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                if (scrollPos >= target.offsetTop && scrollPos < target.offsetTop + target.offsetHeight) {
                    link.classList.add('active');
                    activeFound = true;
                }
            }
        });

        // If nothing else is active, highlight Sākums
        if (!activeFound) {
            const topLink = document.querySelector('.nav-link[href="#top"]');
            if (topLink) topLink.classList.add('active');
        }
    }

    window.addEventListener('scroll', setActiveNav);
    setActiveNav(); // initial call
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentOfferPage = 1;
    const offersContainer = document.querySelector('.pakalpojumi-container');
    const prevBtn = document.getElementById('prevOfferBtn');
    const nextBtn = document.getElementById('nextOfferBtn');

    function loadOffers(page) {
        fetch(`get_offers.php?offers_page=${page}`)
            .then(res => res.json())
            .then(data => {
                offersContainer.innerHTML = '';
                data.offers.forEach(offer => {
                    offersContainer.innerHTML += `
                        <div class="pakalpojums-card">
                            <div class="pakalpojums-icon"><i class="${offer.icon}"></i></div>
                            <h3>${offer.name}</h3>
                            <p>${offer.description}</p>
                            <div class="pakalpojums-footer">
                                <p class="price">Cena: ${parseFloat(offer.price).toFixed(2)} €</p>
                                <button class="btn order-btn" onclick="window.location='payments/checkout_offer.php?offer_id=${offer.id}'">Pasūtīt</button>
                                <p class="order-count">Pasūtījumi: ${offer.orders}</p>
                            </div>
                        </div>
                    `;
                });

                currentOfferPage = page;
                prevBtn.disabled = page <= 1;
                nextBtn.disabled = data.offers.length < 3; // Disable if fewer than 3 offers
            })
            .catch(err => console.error(err));
    }

    prevBtn.addEventListener('click', () => {
        if(currentOfferPage > 1) loadOffers(currentOfferPage - 1);
    });

    nextBtn.addEventListener('click', () => {
        loadOffers(currentOfferPage + 1);
    });

    // Initial load
    loadOffers(currentOfferPage);
});
</script>


</body>
</html>
