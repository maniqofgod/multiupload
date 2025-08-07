<?php
$page_title = 'Ganti Password';
require_once 'includes/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $username = $_SESSION['admin_username'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Password baru dan konfirmasi tidak cocok.';
    } else {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $stmt = $mysqli->prepare("SELECT password_hash FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result && password_verify($current_password, $result['password_hash'])) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $mysqli->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $new_password_hash, $username);
            if ($update_stmt->execute()) {
                $message = 'Password berhasil diubah.';
            } else {
                $error = 'Gagal mengubah password.';
            }
            $update_stmt->close();
        } else {
            $error = 'Password saat ini salah.';
        }
        $mysqli->close();
    }
}
?>
<style>
    .form-group { margin-bottom: 24px; }
    .form-group label { margin-bottom: 8px; font-weight: 500; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 6px; }
    .message { padding: 12px; background-color: #D1FAE5; color: #065F46; border-radius: 6px; margin-bottom: 24px; }
    .error { padding: 12px; background-color: #FEE2E2; color: #991B1B; border-radius: 6px; margin-bottom: 24px; }
</style>

<div class="header">
    <h1>Ganti Password</h1>
</div>

<div class="card" style="max-width: 600px;">
    <?php if ($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
    <form action="change_password.php" method="POST">
        <div class="form-group">
            <label for="current_password">Password Saat Ini</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">Password Baru</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Konfirmasi Password Baru</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Ubah Password</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
