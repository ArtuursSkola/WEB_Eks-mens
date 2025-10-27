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

// Count total users
$countResult = mysqli_query($savienojums, "SELECT COUNT(*) AS total FROM net_users WHERE loma='lietotajs'");
$totalUsers = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalUsers / $limit);

// Fetch paginated users
$sql = "SELECT id, username, vards, uzvards, email, telefons, plan, plan_purchased_at, plan_expires_at, created_at, last_edit_at
        FROM net_users 
        WHERE loma='lietotajs' 
        ORDER BY id ASC 
        LIMIT $limit OFFSET $offset";


$result = mysqli_query($savienojums, $sql);
?>

<?php include("headerr.php"); ?>
<div class="mains">
<main class="admin-container">
    <h1>Klienti</h1>
    <button id="createClientBtn" class="btn" style="float:right; margin-bottom: 1rem;">
        <i class="fas fa-plus"></i> Izveidot jaunu klientu
    </button>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Lietotājvārds</th>
                <th>Vārds</th>
                <th>Uzvārds</th>
                <th>E-pasts</th>
                <th>Telefons</th>
                <th>Plāns</th>
                <th>Termiņš</th>
                <th>Darbība</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                     <?php
        
                        if ($row['plan'] === 'Nav' || !$row['plan_expires_at']) {
                            $days_left = -1;
                        } else {
                            $expiry = new DateTime($row['plan_expires_at']);
                            $now = new DateTime();

                            if ($expiry > $now) {
                                $interval = $now->diff($expiry);
                                $days_left = $interval->days + 1;
                            } else {
                                $days_left = 0; // expired
                            }
                        }


                        ?>
                        <tr data-id="<?php echo $row['id']; ?>" 
                        data-username="<?php echo htmlspecialchars($row['username']); ?>" 
                        data-vards="<?php echo htmlspecialchars($row['vards']); ?>" 
                        data-uzvards="<?php echo htmlspecialchars($row['uzvards']); ?>" 
                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                        data-telefons="<?php echo htmlspecialchars($row['telefons']); ?>" 
                        data-plan="<?php echo htmlspecialchars($row['plan']); ?>"
                        data-created_at="<?php echo $row['created_at']; ?>"
                        data-last_edit_at="<?php echo $row['last_edit_at']; ?>"
                        data-days_left="<?php echo $days_left; ?>">



                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['vards']); ?></td>
                        <td><?php echo htmlspecialchars($row['uzvards']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['telefons']); ?></td>
                        <td><?php echo htmlspecialchars($row['plan']); ?></td>
                        <td class="<?php 
                            if ($row['plan'] === 'Nav' || !$row['plan_purchased_at']) {
                                echo 'inactive';
                            } elseif ($days_left <= 0) {
                                echo 'expired';
                            } else {
                                echo 'active';
                            }
                        ?>">
                        <?php
                            if ($row['plan'] === 'Nav' || !$row['plan_purchased_at']) {
                                echo "Nav aktīvs";
                            } elseif ($days_left > 0) {
                                echo $days_left . " dienas";
                            } else {
                                echo "Beidzies";
                            }
                        ?>
                        </td>

                        <td>
                        <button class="edit-btn"><i class="fas fa-edit"></i></button>
                        <button class="delete-btn"><i class="fa-solid fa-trash"></i></button>

                        <?php if ($days_left > 0 && $days_left <= 3): ?>
                            <!-- Show mail icon for expiring soon -->
                            <button class="mail-btn" title="Send reminder"><i class="fas fa-envelope"></i></button>
                        <?php elseif ($days_left === 0): ?>
                            <!-- Show exclamation icon for expired -->
                            <button class="expired-btn" title="Send expired notice"><i class="fas fa-exclamation-triangle" style="color:red;"></i></button>
                        <?php endif; ?>
                    </td>

                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">Nav lietotāju ar lomu "lietotajs"</td>
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
        <h2>Izveidot jaunu klientu</h2>
        <form id="createForm">
            <label>Lietotājvārds:</label>
            <input type="text" name="username" id="create-username" required>
            <label>Vārds:</label>
            <input type="text" name="vards" id="create-vards" required>
            <label>Uzvards:</label>
            <input type="text" name="uzvards" id="create-uzvards" required>
            <label>E-pasts:</label>
            <input type="email" name="email" id="create-email" required>
            <label>Telefons:</label>
            <input type="text" name="telefons" id="create-telefons">
            <label>Plāns:</label>
            <select name="plan" id="create-plan">
                <option value="Nav">Nav</option>
                <option value="Ekonomiskais plāns">Ekonomiskais plāns</option>
                <option value="Normālais plāns">Normālais plāns</option>
                <option value="Ātrais plāns">Ātrais plāns</option>
            </select>



            <label>Parole:</label>
            <input type="password" name="password" id="create-password" required>
            <button type="submit" class="btn">Izveidot</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Rediģēt lietotāju</h2>
        <form id="editForm">
            <input type="hidden" name="id" id="edit-id">
            <label>Lietotājvārds:</label>
            <input type="text" name="username" id="edit-username" required>
            <label>Vārds:</label>
            <input type="text" name="vards" id="edit-vards" required>
            <label>Uzvards:</label>
            <input type="text" name="uzvards" id="edit-uzvards" required>
            <label>E-pasts:</label>
            <input type="email" name="email" id="edit-email" required>
            <label>Telefons:</label>
            <input type="text" name="telefons" id="edit-telefons">
            <select name="plan" id="edit-plan">
                <option value="Nav">Nav</option>
                <option value="Ekonomiskais plāns">Ekonomiskais plāns</option>
                <option value="Normālais plāns">Normālais plāns</option>
                <option value="Ātrais plāns">Ātrais plāns</option>
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
// ---------------- Edit Modal ----------------
// ---------------- Edit Modal ----------------
const editModal = document.getElementById('editModal');
const editClose = editModal.querySelector('.close');
const editForm = document.getElementById('editForm');

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        const tr = e.target.closest('tr');  // Must be defined here

        document.getElementById('edit-id').value = tr.dataset.id;
        document.getElementById('edit-username').value = tr.dataset.username;
        document.getElementById('edit-vards').value = tr.dataset.vards;
        document.getElementById('edit-uzvards').value = tr.dataset.uzvards;
        document.getElementById('edit-email').value = tr.dataset.email;
        document.getElementById('edit-telefons').value = tr.dataset.telefons;
        document.getElementById('edit-plan').value = tr.dataset.plan || 'Nav';

        // Populate timestamps with Latvian date & time
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
        username: document.getElementById('edit-username').value,
        vards: document.getElementById('edit-vards').value,
        uzvards: document.getElementById('edit-uzvards').value,
        email: document.getElementById('edit-email').value,
        telefons: document.getElementById('edit-telefons').value,
        plan: document.getElementById('edit-plan').value
    };
    const response = await fetch(`api/klienti_api.php?id=${id}`, {
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
        if(!confirm("Vai tiešām dzēst šo lietotāju?")) return;
        const tr = e.target.closest('tr');
        const id = tr.dataset.id;
        const response = await fetch(`api/klienti_api.php?id=${id}`, { method: 'DELETE' });
        const result = await response.json();
        alert(result.message || result.error);
        if(response.ok) tr.remove();
    });
});

// ---------------- Create Modal ----------------
const createModal = document.getElementById('createModal');
const createBtn = document.getElementById('createClientBtn');
const createClose = createModal.querySelector('.close');
const createForm = document.getElementById('createForm');

createBtn.addEventListener('click', () => createModal.style.display = 'flex');
createClose.addEventListener('click', () => createModal.style.display = 'none');
window.addEventListener('click', e => { if(e.target === createModal) createModal.style.display = 'none'; });

// Handle Create Submit
createForm.addEventListener('submit', async e => {
    e.preventDefault();
    const data = {
        username: document.getElementById('create-username').value,
        vards: document.getElementById('create-vards').value,
        uzvards: document.getElementById('create-uzvards').value,
        email: document.getElementById('create-email').value,
        telefons: document.getElementById('create-telefons').value,
        plan: document.getElementById('create-plan').value,
        password: document.getElementById('create-password').value,
        loma: 'lietotajs'
    };
    const response = await fetch('api/klienti_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
        credentials: 'include'   // <-- important
    });

const text = await response.text();
console.log("Raw response:", text);

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
