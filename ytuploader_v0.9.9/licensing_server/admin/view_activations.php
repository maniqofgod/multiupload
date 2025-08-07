<?php
$page_title = 'Lihat Aktivasi';
require_once 'includes/header.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!isset($_GET['license_id']) || !is_numeric($_GET['license_id'])) {
    die("ID Lisensi tidak valid.");
}
$license_id = (int)$_GET['license_id'];

$license_info_stmt = $mysqli->prepare("SELECT license_key FROM licenses WHERE id = ?");
$license_info_stmt->bind_param("i", $license_id);
$license_info_stmt->execute();
$license_key = $license_info_stmt->get_result()->fetch_assoc()['license_key'];
$license_info_stmt->close();

$stmt = $mysqli->prepare("SELECT * FROM activations WHERE license_id = ? ORDER BY activated_at DESC");
$stmt->bind_param("i", $license_id);
$stmt->execute();
$activations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="header">
    <h1>Aktivasi untuk Lisensi</h1>
    <a href="manage_licenses.php" class="btn" style="background-color: #6c757d;">Kembali</a>
</div>
<p><code><?php echo htmlspecialchars($license_key); ?></code></p>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>HWID</th>
                <th>Tanggal Aktivasi</th>
                <th>Terakhir Dilihat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($activations)): ?>
                <tr><td colspan="4" style="text-align: center;">Belum ada aktivasi.</td></tr>
            <?php else: ?>
                <?php foreach ($activations as $activation): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($activation['hwid']); ?></code></td>
                        <td><?php echo htmlspecialchars($activation['activated_at']); ?></td>
                        <td><?php echo htmlspecialchars($activation['last_seen_at']); ?></td>
                        <td>
                            <a href="deactivate_hwid_action.php?id=<?php echo $activation['id']; ?>" class="btn" style="background-color: #dc3545;" onclick="return confirm('Anda yakin?');">Deaktivasi</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$mysqli->close();
require_once 'includes/footer.php';
?>
