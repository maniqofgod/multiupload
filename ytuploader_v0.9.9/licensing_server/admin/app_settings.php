<?php
$page_title = 'Pengaturan Aplikasi';
require_once 'includes/header.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings_to_update = [
        'latest_version', 'download_url', 'release_notes_url',
        'is_mandatory_update', 'update_message', 'maintenance_mode', 'maintenance_message'
    ];
    $stmt = $mysqli->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = ?");
    foreach ($settings_to_update as $key) {
        // Handle checkbox value for boolean settings
        if ($key === 'is_mandatory_update' || $key === 'maintenance_mode') {
            $value = isset($_POST[$key]) ? '1' : '0';
        } else {
            $value = $_POST[$key] ?? '';
        }
        $stmt->bind_param("ss", $value, $key);
        $stmt->execute();
    }
    $stmt->close();
    $message = 'Pengaturan berhasil diperbarui!';
}

$settings = [];
$result = $mysqli->query("SELECT * FROM app_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$mysqli->close();
?>
<style>
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { margin-bottom: 8px; font-weight: 500; color: #374151; }
    .form-group input, .form-group select, .form-group textarea {
        padding: 10px; border: 1px solid #D1D5DB; border-radius: 6px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); outline: none;
    }
    .form-switch { display: flex; align-items: center; gap: 12px; }
    .form-switch label { margin-bottom: 0; }
    .toggle-switch { position: relative; display: inline-block; width: 50px; height: 28px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 28px; }
    .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: var(--primary-color); }
    input:checked + .slider:before { transform: translateX(22px); }
    .message { padding: 12px; background-color: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; border-radius: 6px; margin-bottom: 24px; }
</style>

<div class="header">
    <h1>Pengaturan Aplikasi</h1>
</div>

<?php if ($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>

<form action="app_settings.php" method="POST">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 24px;">
            <h2 style="margin:0;">Info Update</h2>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label for="latest_version">Versi Terbaru</label>
                <input type="text" id="latest_version" name="latest_version" value="<?php echo htmlspecialchars($settings['latest_version'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="download_url">URL Unduh</label>
                <input type="text" id="download_url" name="download_url" value="<?php echo htmlspecialchars($settings['download_url'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group" style="margin-top: 24px;">
            <label for="update_message">Pesan Update</label>
            <textarea id="update_message" name="update_message" rows="3"><?php echo htmlspecialchars($settings['update_message'] ?? ''); ?></textarea>
        </div>
        <div class="form-switch" style="margin-top: 24px;">
            <label class="toggle-switch">
                <input type="checkbox" name="is_mandatory_update" value="1" <?php echo (($settings['is_mandatory_update'] ?? '0') == '1') ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
            <label for="is_mandatory_update">Jadikan Update Wajib</label>
        </div>
    </div>

    <div class="card" style="margin-top: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 24px;">
            <h2 style="margin:0;">Mode Pemeliharaan</h2>
        </div>
        <div class="form-switch">
            <label class="toggle-switch">
                <input type="checkbox" name="maintenance_mode" value="1" <?php echo (($settings['maintenance_mode'] ?? '0') == '1') ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
            <label for="maintenance_mode">Aktifkan Mode Pemeliharaan</label>
        </div>
        <div class="form-group" style="margin-top: 24px;">
            <label for="maintenance_message">Pesan Pemeliharaan</label>
            <textarea id="maintenance_message" name="maintenance_message" rows="3"><?php echo htmlspecialchars($settings['maintenance_message'] ?? ''); ?></textarea>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary" style="margin-top: 32px; padding: 12px 24px; font-size: 1rem;">Simpan Semua Pengaturan</button>
</form>

<?php require_once 'includes/footer.php'; ?>
