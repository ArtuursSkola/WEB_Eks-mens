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

$perPage = 15; // max 15 entries per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Get total reviews count
$totalResult = mysqli_query($savienojums, "SELECT COUNT(*) AS total FROM net_reviews");
$totalRow = mysqli_fetch_assoc($totalResult);
$totalReviews = $totalRow['total'];

// Fetch reviews for current page
$sql = "SELECT id, name, review, stars, created_at, last_edit_at, bilde 
        FROM net_reviews 
        ORDER BY created_at DESC 
        LIMIT $perPage OFFSET $offset";
$result = mysqli_query($savienojums, $sql);

// Calculate if there is a next page
$hasNext = ($offset + $perPage) < $totalReviews;
$hasPrev = $page > 1;


?>

<?php include("headerr.php"); ?>
<div class="mains">
<main class="admin-container">

    <h1>Atsauksmes</h1>
    <button id="createReviewBtn" class="btn" style="float:right; margin-bottom:1rem;">
    <i class="fas fa-plus"></i> Pievienot jaunu atsauksmi
</button>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Vārds</th>
                <th>Atsauksme</th>
                <th>Zvaigznes</th>
                <th>Attēls</th> <!-- NEW -->
                <th>Datums</th>
                <th>Darbība</th>
            </tr>
        </thead>
<tbody>
<?php if(mysqli_num_rows($result) > 0): ?>
    <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr data-id="<?php echo $row['id']; ?>" 
            data-name="<?php echo htmlspecialchars($row['name']); ?>" 
            data-review="<?php echo htmlspecialchars($row['review']); ?>" 
            data-stars="<?php echo $row['stars']; ?>"
            data-created_at="<?php echo $row['created_at']; ?>"
            data-last_edit_at="<?php echo $row['last_edit_at']; ?>">

            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($row['review'])); ?></td>
            <td>
                <?php
                for($i = 1; $i <= 5; $i++) {
                    echo $i <= $row['stars'] ? "★" : "☆";
                }
                ?>
            </td>
                <td>
                    <?php if (!empty($row['bilde'])): ?>
                        <img src="../view_image.php?id=<?php echo $row['id']; ?>" alt="Atsauksmes bilde" width="100" style="border-radius:6px;">
                    <?php else: ?>
                        <span>Nav attēla</span>
                    <?php endif; ?>
                </td>

            <td>
    <?php
    $date = new DateTime($row['created_at']);
    echo $date->format('d.m.Y H:i');
    ?>
</td>

            <td>
                <button class="edit-btn"><i class="fas fa-edit"></i></button>
                <button class="delete-btn"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="7">Nav atsauksmju</td>
    </tr>
<?php endif; ?>
</tbody>

    </table>
</main>
<div class="pagination" style="margin-top:1rem;">
    <?php if($hasPrev): ?>
        <a href="?page=<?= $page - 1 ?>" class="btn">&laquo; Iepriekšējā lapa</a>
    <?php endif; ?>

    <?php if($hasNext): ?>
        <a href="?page=<?= $page + 1 ?>" class="btn">Nākošā lapa &raquo;</a>
    <?php endif; ?>
</div>



<div id="createModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Pievienot jaunu atsauksmi</h2>
        <form id="createForm" enctype="multipart/form-data">
            <label>Vārds:</label>
            <input type="text" name="name" id="create-name" required>
            <label>Atsauksme:</label>
            <textarea name="review" id="create-review" required></textarea>
            <label>Zvaigznes:</label>
            <select name="stars" id="create-stars" required>
                <option value="1">1 ★</option>
                <option value="2">2 ★★</option>
                <option value="3">3 ★★★</option>
                <option value="4">4 ★★★★</option>
                <option value="5">5 ★★★★★</option>
            </select>
            <button type="submit" class="btn">Pievienot</button>
        </form>
    </div>
</div>
<!-- Edit Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Rediģēt atsauksmi</h2>
        <form id="editForm">
            <input type="hidden" name="id" id="edit-id">
            <label>Vārds:</label>
            <input type="text" name="name" id="edit-name" required>
            <label>Atsauksme:</label>
            <textarea name="review" id="edit-review" required></textarea>
            <label>Zvaigznes:</label>
            <select name="stars" id="edit-stars" required>
                <option value="1">1 ★</option>
                <option value="2">2 ★★</option>
                <option value="3">3 ★★★</option>
                <option value="4">4 ★★★★</option>
                <option value="5">5 ★★★★★</option>
            </select>
            <button type="submit" class="btn">Saglabāt</button>
              <div class="timestamps" style="display:flex; justify-content:space-between; margin-top:10px; font-size:0.85rem; color:#555;">
    <span id="createdAt"></span>
    <span id="lastEditAt"></span>
</div>
        </form>
    </div>
</div>

<style>
.admin-container {
    padding: 2rem;
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
}

.admin-container table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto; /* allows columns to size naturally */
}

.admin-container th,
.admin-container td {
    border: 1px solid #ccc;
    padding: 0.75rem 1rem;
    text-align: left;
    word-wrap: break-word;
    white-space: normal;
}

.admin-container th {
    background-color: #05b823;
    color: #fff;
}

.admin-container td {
    background-color: #fff;
}

i { color: var(--main); }
footer {margin-top: 0;}
button { background: none; border: none; cursor: pointer; font-size: 1rem; margin-right: 5px; }
.modal { position: fixed; top: 0; left: 0; width:100%; height:100%; background: rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; }
.modal-content { background:#fff; padding:2rem; border-radius:8px; width:400px; position:relative; }
.modal-content .close { position:absolute; top:10px; right:10px; font-size:1.5rem; cursor:pointer; }
</style>

<script>
// Modal handling
const modal = document.getElementById('editModal');
const closeBtn = modal.querySelector('.close');
const editForm = document.getElementById('editForm');

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        const tr = e.target.closest('tr');

        document.getElementById('edit-id').value = tr.dataset.id;
        document.getElementById('edit-name').value = tr.dataset.name;
        document.getElementById('edit-review').value = tr.dataset.review;
        document.getElementById('edit-stars').value = tr.dataset.stars;

        // Display timestamps
       document.getElementById('createdAt').textContent = "Izveidots: " + 
    new Date(tr.dataset.created_at).toLocaleString('lv-LV', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    }).replace(',', ''); // removes the comma between date and time

document.getElementById('lastEditAt').textContent = tr.dataset.last_edit_at
    ? "Pēdējoreiz rediģēts: " + new Date(tr.dataset.last_edit_at).toLocaleString('lv-LV', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    }).replace(',', '')
    : "Nav rediģēts";

        modal.style.display = 'flex';
    });
});



closeBtn.onclick = () => modal.style.display = 'none';
window.onclick = e => { if(e.target === modal) modal.style.display = 'none'; };

// Handle Edit Submit
editForm.addEventListener('submit', async e => {
    e.preventDefault();
    const id = document.getElementById('edit-id').value;
    const data = {
        name: document.getElementById('edit-name').value,
        review: document.getElementById('edit-review').value,
        stars: document.getElementById('edit-stars').value
    };

    const response = await fetch(`api/atsauksmes_api.php?id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    const result = await response.json();
    if(response.ok){
        alert(result.message);
        location.reload();
    } else {
        alert(result.error);
    }
});

// Handle Delete
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', async e => {
        if(!confirm("Vai tiešām dzēst šo atsauksmi?")) return;
        const tr = e.target.closest('tr');
        const id = tr.dataset.id;

        const response = await fetch(`api/atsauksmes_api.php?id=${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        if(response.ok){
            alert(result.message);
            tr.remove();
        } else {
            alert(result.error);
        }
    });
});

// ---------------- Create Modal ----------------
const createModal = document.getElementById('createModal');
const createBtn = document.getElementById('createReviewBtn');
const createClose = createModal.querySelector('.close');
const createForm = document.getElementById('createForm');

createBtn.addEventListener('click', () => createModal.style.display = 'flex');
createClose.addEventListener('click', () => createModal.style.display = 'none');
window.addEventListener('click', e => { if(e.target === createModal) createModal.style.display = 'none'; });

// Handle Create Submit
createForm.addEventListener('submit', async e => {
    e.preventDefault();

    const formData = new FormData(createForm); // includes file input

    const response = await fetch('api/atsauksmes_api.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });

    const text = await response.text();
    console.log("Raw response:", text);

    // Attempt to parse JSON
    let result;
    try {
        result = JSON.parse(text);
    } catch(e) {
        alert("Server returned invalid JSON: " + text);
        return;
    }

    alert(result.message || result.error);

    if (response.ok) location.reload();
});


</script>
</div>
<?php include("footer.php"); ?>
</body>
</html>
