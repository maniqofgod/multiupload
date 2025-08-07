<?php
$page_title = 'Edit Lisensi';
require_once 'includes/header.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$license = [
    'id' => '', 'license_key' => '', 'status' => 'active', 'expiry_date' => '',
    'customer_name' => '', 'notes' => '', 'max_devices' => 1
];
$page_title = 'Tambah Lisensi Baru';
$is_edit_mode = false;

if (isset($_GET['id'])) {
    $is_edit_mode = true;
    $id = (int)$_GET['id'];
    $stmt = $mysqli->prepare("SELECT * FROM licenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $license = $result->fetch_assoc();
        $page_title = 'Edit Lisensi';
    } else {
        die("Lisensi tidak ditemukan.");
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $license_key = $_POST['license_key'];
    $status = $_POST['status'];
    $expiry_date = empty($_POST['expiry_date']) ? null : $_POST['expiry_date'];
    $customer_name = $_POST['customer_name'];
    $notes = $_POST['notes'];
    $max_devices = (int)$_POST['max_devices'];

    if ($id) {
        $stmt = $mysqli->prepare("UPDATE licenses SET license_key = ?, status = ?, expiry_date = ?, customer_name = ?, notes = ?, max_devices = ? WHERE id = ?");
        $stmt->bind_param("sssssii", $license_key, $status, $expiry_date, $customer_name, $notes, $max_devices, $id);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO licenses (license_key, status, expiry_date, customer_name, notes, max_devices) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $license_key, $status, $expiry_date, $customer_name, $notes, $max_devices);
    }

    if ($stmt->execute()) {
        header('Location: manage_licenses.php');
        exit;
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

function generate_license_key() {
    $parts = [];
    for ($i = 0; $i < 5; $i++) {
        $parts[] = strtoupper(bin2hex(random_bytes(2)));
    }
    return implode('-', $parts);
}

if (!$is_edit_mode) {
    $license['license_key'] = generate_license_key();
}
?>
<style>
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { margin-bottom: 8px; font-weight: 500; color: #374151; }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 6px;
        box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); outline: none;
    }
    .full-width { grid-column: 1 / -1; }
    .btn-secondary { background-color: #6B7280; }
    .btn-secondary:hover { background-color: #4B5563; }
</style>

<div class="header">
    <h1><?php echo $page_title; ?></h1>
</div>

<div class="card">
    <?php if (isset($error)): ?><p style="color: red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <form action="edit_license.php<?php echo $is_edit_mode ? '?id='.$license['id'] : ''; ?>" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($license['id']); ?>">
        
        <div class="form-grid">
            <div class="form-group full-width">
                <label for="license_key">Kunci Lisensi</label>
                <input type="text" id="license_key" name="license_key" value="<?php echo htmlspecialchars($license['license_key']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="customer_name">Nama Pelanggan</label>
                <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($license['customer_name']); ?>">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?php echo ($license['status'] == 'active') ? 'selected' : ''; ?>>Aktif</option>
                    <option value="pending" <?php echo ($license['status'] == 'pending') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="expired" <?php echo ($license['status'] == 'expired') ? 'selected' : ''; ?>>Kedaluwarsa</option>
                    <option value="disabled" <?php echo ($license['status'] == 'disabled') ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="expiry_date">Tanggal Kedaluwarsa (opsional)</label>
                <input type="date" id="expiry_date" name="expiry_date" value="<?php echo htmlspecialchars($license['expiry_date']); ?>">
            </div>
            
            <div class="form-group">
                <label for="max_devices">Maksimum Perangkat</label>
                <input type="number" id="max_devices" name="max_devices" value="<?php echo htmlspecialchars($license['max_devices']); ?>" min="1" required>
            </div>
            
            <div class="form-group full-width">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($license['notes']); ?></textarea>
            </div>
        </div>
        
        <div style="margin-top: 24px;">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="manage_licenses.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php
$mysqli->close();
require_once 'includes/footer.php';
?>
