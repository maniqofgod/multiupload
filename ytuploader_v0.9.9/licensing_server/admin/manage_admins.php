<?php
$page_title = 'Kelola Admin';
require_once 'includes/header.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$username = $_SESSION['admin_username'];
$stmt_role = $mysqli->prepare("SELECT role FROM admins WHERE username = ?");
$stmt_role->bind_param("s", $username);
$stmt_role->execute();
$user_role = $stmt_role->get_result()->fetch_assoc()['role'];
$stmt_role->close();

if ($user_role !== 'superadmin') {
    echo "<div class='header'><h1>Akses Ditolak</h1></div><div class='card'><p>Anda tidak memiliki izin untuk mengakses halaman ini.</p></div>";
    require_once 'includes/footer.php';
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $new_user = $_POST['new_username'];
    $new_pass = $_POST['new_password'];
    $new_role = $_POST['new_role'];
    if (!empty($new_user) && !empty($new_pass)) {
        $pass_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $new_user, $pass_hash, $new_role);
        if ($stmt->execute()) {
            $message = "Admin '$new_user' berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan admin: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Username dan password tidak boleh kosong.";
    }
}

if (isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    if ($id_to_delete !== 1) {
        $stmt = $mysqli->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->bind_param("i", $id_to_delete);
        if ($stmt->execute()) {
            $message = "Admin berhasil dihapus.";
        } else {
            $error = "Gagal menghapus admin.";
        }
        $stmt->close();
    } else {
        $error = "Superadmin utama tidak dapat dihapus.";
    }
}

$admins = $mysqli->query("SELECT id, username, role, created_at FROM admins")->fetch_all(MYSQLI_ASSOC);
?>
<style>
    .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 16px; align-items: flex-end; }
    .form-group label { margin-bottom: 8px; font-weight: 500; }
    .form-group input, .form-group select {
        width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 6px;
    }
    .btn-delete { color: #EF4444; text-decoration: none; font-weight: 500; }
    .message { padding: 12px; background-color: #D1FAE5; color: #065F46; border-radius: 6px; margin-bottom: 24px; }
    .error { padding: 12px; background-color: #FEE2E2; color: #991B1B; border-radius: 6px; margin-bottom: 24px; }
</style>

<div class="header">
    <h1>Kelola Admin</h1>
</div>

<?php if ($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>

<div class="card" style="margin-bottom: 32px;">
    <h2>Tambah Admin Baru</h2>
    <form action="manage_admins.php" method="POST">
        <input type="hidden" name="add_admin" value="1">
        <div class="form-grid">
            <div class="form-group">
                <label for="new_username">Username</label>
                <input type="text" id="new_username" name="new_username" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="new_role">Role</label>
                <select id="new_role" name="new_role">
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tambah</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Daftar Admin</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $admin): ?>
            <tr>
                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                <td><?php echo htmlspecialchars($admin['role']); ?></td>
                <td><?php echo $admin['created_at']; ?></td>
                <td>
                    <?php if ($admin['id'] !== 1): ?>
                    <a href="manage_admins.php?delete_id=<?php echo $admin['id']; ?>" class="btn-delete" onclick="return confirm('Anda yakin?');">Hapus</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$mysqli->close();
require_once 'includes/footer.php';
?>
