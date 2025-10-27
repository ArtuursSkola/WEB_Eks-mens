<?php
session_start();
include("../con_db.php");

// Only allow admins or moderators
if (!isset($_SESSION['username']) || 
    !isset($_SESSION['loma']) || 
    !in_array($_SESSION['loma'], ['administrators', 'moderators'])) {
    
    header("Location: ../index.php");
    exit;
}

// Get today's date
$today = date('Y-m-d');

// Count submitted applications (you didn’t have this query, so set it to 0 for now)
//reviews
// Count reviews published today
$reviews_sql = "SELECT COUNT(*) as count FROM net_reviews WHERE DATE(created_at) = '$today'";
$reviews_result = mysqli_query($savienojums, $reviews_sql);
$reviews_count = mysqli_fetch_assoc($reviews_result)['count'] ?? 0;
$app_count = $reviews_count;
// Count users who bought a plan today (plan != 'Nav')
// ✅ Count users who purchased or renewed a plan today
$sub_sql = "
    SELECT COUNT(*) AS count
    FROM net_users
    WHERE plan <> 'Nav'
    AND DATE(plan_purchased_at) = '$today'
";
$sub_result = mysqli_query($savienojums, $sub_sql);
$sub_count = mysqli_fetch_assoc($sub_result)['count'] ?? 0;



$acc_sql = "SELECT COUNT(*) as count 
            FROM net_offers 
            WHERE DATE(last_order_at) = '$today'";
$acc_result = mysqli_query($savienojums, $acc_sql);
$acc_count = mysqli_fetch_assoc($acc_result)['count'] ?? 0;



// Get review stars distribution
$stars_sql = "SELECT stars, COUNT(*) as count FROM net_reviews GROUP BY stars ORDER BY stars";
$stars_result = mysqli_query($savienojums, $stars_sql);

$stars_data = [];
while($row = mysqli_fetch_assoc($stars_result)){
    $stars_data[$row['stars']] = $row['count'];
}

// Fill missing star counts
for($i=1;$i<=5;$i++){
    if(!isset($stars_data[$i])) $stars_data[$i] = 0;
}

// Users registered in the last 7 days
$users_last_7_days = [];
$dates_last_7_days = [];

for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $dates_last_7_days[] = $day;

    $sql = "SELECT COUNT(*) as count FROM net_users WHERE DATE(created_at) = '$day'";
    $result = mysqli_query($savienojums, $sql);
    $users_last_7_days[] = mysqli_fetch_assoc($result)['count'] ?? 0;
}

?>
<?php
$current_page = basename($_SERVER['PHP_SELF']);

include("header.php");
?>

<div class="main">
<main class="admin-container">
    <div class="top-h">
    <h2><i class='fas fa-chart-line'></i> Sistēmas statistikas dati</h2>
    </div>
<div class="boxes-container">
     <div class="box" id="intro-box">
        <i class="fas fa-user icon"></i>
        <div>
            <h2>Sveicināti sistēmā, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            <h4>Loma: <?php echo htmlspecialchars($_SESSION['loma']); ?></h4>
        </div>
    </div>
    <div class="box">
        <i class="fas fa-file-alt icon"></i>
        <div>
            <h2>Iesniegtas atsauksmes šodien</h2>
            <p><?php echo $app_count; ?></p>
        </div>
    </div>
    <div class="box">
        <i class="fas fa-credit-card icon"></i>
        <div>
            <h2>Abonomentu iegāde šodien</h2>
            <p><?php echo $sub_count; ?></p>
        </div>
    </div>
    <div class="box">
        <i class="fas fa-user-plus icon"></i>
        <div>
            <h2>Pakalpojumu iegāde šodien</h2>
            <p><?php echo $acc_count; ?></p>
        </div>
    </div>
</div>

    <div class="charts-row">
    <div class="chart-container" id="pirags">
        <h2><i class="fa-solid fa-star"></i> Atsauksmju zvaigžņu sadalījums</h2>
        <canvas id="starsChart"></canvas>
    </div>
    <div class="chart-container">
        <h2><i class="fa fa-plus" aria-hidden="true"></i> Konta izveides pēdējās 7 dienas</h2>
        <canvas id="usersChart"></canvas>
    </div>
    </div>
</main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textColor = getComputedStyle(document.body).getPropertyValue('--chart-text').trim();

    const ctx = document.getElementById('starsChart').getContext('2d');
    const starsChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['1 ★', '2 ★★', '3 ★★★', '4 ★★★★', '5 ★★★★★'],
            datasets: [{
                data: [
                    <?php echo $stars_data[1]; ?>,
                    <?php echo $stars_data[2]; ?>,
                    <?php echo $stars_data[3]; ?>,
                    <?php echo $stars_data[4]; ?>,
                    <?php echo $stars_data[5]; ?>
                ],
                backgroundColor: ['#ff4d4f','#ff7a45','#ffec3d','#73d13d','#36cfc9']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor } },
                title: { display: true, text: 'Atsauksmju zvaigznes', color: textColor }
            }
        }
    });

    const usersCtx = document.getElementById('usersChart').getContext('2d');
    const usersChart = new Chart(usersCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates_last_7_days); ?>,
            datasets: [{
                label: 'Jaunie lietotāji',
                data: <?php echo json_encode($users_last_7_days); ?>,
                borderColor: '#05b823',
                backgroundColor: 'rgba(5,184,35,0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { color: textColor } },
                title: { display: true, text: 'Jaunie lietotāji pēdējās 7 dienās', color: textColor }
            },
            scales: {
                x: { ticks: { color: textColor } },
                y: { ticks: { color: textColor }, beginAtZero: true, precision: 0 }
            }
        }
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const mobileDropdowns = document.querySelectorAll('.mobile-dropdown');

    mobileDropdowns.forEach(dropdown => {
        const btn = dropdown.querySelector('.admin-menu-btn');
        const content = dropdown.querySelector('.dropdown-content');

        btn.addEventListener('click', (e) => {
            e.stopPropagation(); // prevent window click from closing
            content.classList.toggle('show');
        });

        // Close dropdown if clicked outside
        window.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target)) {
                content.classList.remove('show');
            }
        });

        // Close dropdown on scroll
        window.addEventListener('scroll', () => {
            content.classList.remove('show');
        });
    });
});


</script>
<?php 
include("footer.php");
?>
</body>
</html>
