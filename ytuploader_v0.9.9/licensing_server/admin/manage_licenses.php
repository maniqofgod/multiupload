<?php
$page_title = 'Kelola Lisensi';
require_once 'includes/header.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$sql = "SELECT l.*, COUNT(a.id) as activation_count FROM licenses l LEFT JOIN activations a ON l.id = a.license_id GROUP BY l.id ORDER BY l.created_at DESC";
$licenses = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<div class="header">
    <h1>Kelola Lisensi</h1>
    <a href="edit_license.php" class="btn btn-primary">Tambah Lisensi</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Kunci Lisensi</th>
                <th>Pelanggan</th>
                <th>Status</th>
                <th>Aktivasi</th>
                <th>Kedaluwarsa</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($licenses)): ?>
                <tr><td colspan="6" style="text-align: center;">Belum ada lisensi.</td></tr>
            <?php else: ?>
                <?php foreach ($licenses as $license): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($license['license_key']); ?></code></td>
                        <td><?php echo htmlspecialchars($license['customer_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($license['status']); ?></td>
                        <td><?php echo htmlspecialchars($license['activation_count']); ?> / <?php echo htmlspecialchars($license['max_devices']); ?></td>
                        <td><?php echo htmlspecialchars($license['expiry_date'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="view_activations.php?license_id=<?php echo $license['id']; ?>" class="btn" style="background-color: #17a2b8;">Lihat HWID</a>
                            <a href="edit_license.php?id=<?php echo $license['id']; ?>" class="btn" style="background-color: #ffc107;">Edit</a>
                            <a href="delete_license.php?id=<?php echo $license['id']; ?>" class="btn" style="background-color: #dc3545;" onclick="return confirm('Anda yakin?');">Hapus</a>
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
