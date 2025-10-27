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

$limit = 15; // max entries per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total offers
$countResult = mysqli_query($savienojums, "SELECT COUNT(*) AS total FROM net_offers");
$totalOffers = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalOffers / $limit);

// Fetch paginated offers
$sql = "SELECT id, name, description, price, icon, orders, created_at, last_edit_at 
        FROM net_offers 
        ORDER BY id ASC 
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($savienojums, $sql);
?>

<?php include("headerr.php"); ?>
<div class="mains">
<main class="admin-container">
    <h1>Piedāvājumi</h1>
    <button id="createOfferBtn" class="btn" style="float:right; margin-bottom: 1rem;">
        <i class="fas fa-plus"></i> Izveidot jaunu piedāvājumu
    </button>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nosaukums</th>
                <th>Apraksts</th>
                <th>Cena</th>
                <th>Ikona</th>
                <th>Pasūtījumi</th>
                <th>Darbība</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr data-id="<?php echo $row['id']; ?>"
                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                    data-description="<?php echo htmlspecialchars($row['description']); ?>"
                    data-price="<?php echo $row['price']; ?>"
                    data-icon="<?php echo htmlspecialchars($row['icon']); ?>"
                    data-orders="<?php echo $row['orders']; ?>"
                    data-created_at="<?php echo $row['created_at']; ?>"
                    data-last_edit_at="<?php echo $row['last_edit_at']; ?>">
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo $row['price']; ?> €</td>
                        <td><i class="<?php echo htmlspecialchars($row['icon']); ?>"></i></td>
                        <td><?php echo $row['orders']; ?></td>
                        <td>
                            <button class="edit-btn"><i class="fas fa-edit"></i></button>
                            <button class="delete-btn"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Nav piedāvājumu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<div class="pagination" style="margin-top:1rem;">
<?php if($page > 1): ?>
    <a href="?page=<?php echo $page-1; ?>" class="btn">Iepriekšējā lapa</a>
<?php endif; ?>
<?php if($page < $totalPages): ?>
    <a href="?page=<?php echo $page+1; ?>" class="btn">Nākošā lapa</a>
<?php endif; ?>
</div>

<!-- Create Modal -->
<div id="createModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Izveidot jaunu piedāvājumu</h2>
        <form id="createForm">
            <label>Nosaukums:</label>
            <input type="text" name="name" id="create-name" required>
            <label>Apraksts:</label>
            <textarea name="description" id="create-description" required></textarea>
            <label>Cena:</label>
            <input type="number" step="0.01" name="price" id="create-price" required>
            <label>Ikona (FontAwesome klases):</label>
            <input type="text" name="icon" id="create-icon">
            <label>Pasūtījumi:</label>
            <input type="number" name="orders" id="create-orders" value="0">
            <button type="submit" class="btn">Izveidot</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Rediģēt piedāvājumu</h2>
        <form id="editForm">
            <input type="hidden" name="id" id="edit-id">
            <label>Nosaukums:</label>
            <input type="text" name="name" id="edit-name" required>
            <label>Apraksts:</label>
            <textarea name="description" id="edit-description" required></textarea>
            <label>Cena:</label>
            <input type="number" step="0.01" name="price" id="edit-price" required>
            <label>Ikona (FontAwesome klases):</label>
            <input type="text" name="icon" id="edit-icon">
            <label>Pasūtījumi:</label>
            <input type="number" name="orders" id="edit-orders">
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
// ---------------- Edit Modal ----------------
const editModal = document.getElementById('editModal');
const editClose = editModal.querySelector('.close');
const editForm = document.getElementById('editForm');

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        const tr = e.target.closest('tr');

        document.getElementById('edit-id').value = tr.dataset.id;
        document.getElementById('edit-name').value = tr.dataset.name;
        document.getElementById('edit-description').value = tr.dataset.description;
        document.getElementById('edit-price').value = tr.dataset.price;
        document.getElementById('edit-icon').value = tr.dataset.icon;
        document.getElementById('edit-orders').value = tr.dataset.orders;

        // ✅ Format dates in Latvian short style
        document.getElementById('createdAt').textContent =
            "Izveidots: " +
            new Date(tr.dataset.created_at).toLocaleString('lv-LV', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).replace(',', '');

        document.getElementById('lastEditAt').textContent = tr.dataset.last_edit_at
            ? "Pēdējoreiz rediģēts: " +
              new Date(tr.dataset.last_edit_at).toLocaleString('lv-LV', {
                  day: '2-digit',
                  month: '2-digit',
                  year: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
              }).replace(',', '')
            : "Nav rediģēts";

        editModal.style.display = 'flex';
    });
});

editClose.onclick = () => editModal.style.display = 'none';
window.onclick = e => { if(e.target === editModal) editModal.style.display = 'none'; };

// Handle Edit Submit
editForm.addEventListener('submit', async e => {
    e.preventDefault();
    const id = document.getElementById('edit-id').value;
    const data = {
        name: document.getElementById('edit-name').value,
        description: document.getElementById('edit-description').value,
        price: document.getElementById('edit-price').value,
        icon: document.getElementById('edit-icon').value,
        orders: document.getElementById('edit-orders').value
    };
    const response = await fetch(`api/piedavajumi_api.php?id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    const result = await response.json();
    alert(result.message || result.error);
    if(response.ok) location.reload();
});

// ---------------- Delete ----------------
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', async e => {
        if(!confirm("Vai tiešām dzēst šo piedāvājumu?")) return;
        const tr = e.target.closest('tr');
        const id = tr.dataset.id;
        const response = await fetch(`api/piedavajumi_api.php?id=${id}`, { method: 'DELETE' });
        const result = await response.json();
        alert(result.message || result.error);
        if(response.ok) tr.remove();
    });
});

// ---------------- Create Modal ----------------
const createModal = document.getElementById('createModal');
const createBtn = document.getElementById('createOfferBtn');
const createClose = createModal.querySelector('.close');
const createForm = document.getElementById('createForm');

createBtn.addEventListener('click', () => createModal.style.display = 'flex');
createClose.addEventListener('click', () => createModal.style.display = 'none');
window.addEventListener('click', e => { if(e.target === createModal) createModal.style.display = 'none'; });

// Handle Create Submit
createForm.addEventListener('submit', async e => {
    e.preventDefault();
    const data = {
        name: document.getElementById('create-name').value,
        description: document.getElementById('create-description').value,
        price: document.getElementById('create-price').value,
        icon: document.getElementById('create-icon').value,
        orders: document.getElementById('create-orders').value
    };
    const response = await fetch('api/piedavajumi_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
        credentials: 'include'
    });
    const result = await response.json();
    alert(result.message || result.error);
    if (response.ok) location.reload();
});
</script>
</div>

<?php include("footer.php"); ?>
</body>
</html>
