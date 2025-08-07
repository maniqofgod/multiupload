<?php
$page_title = 'Notifikasi Marketing';
require_once 'includes/header.php';

$notifications_file = '../downloads/marketing_notifications.json';
$message = '';
$error = '';

$settings = [];
if (file_exists($notifications_file)) {
    $settings = json_decode(file_get_contents($notifications_file), true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_settings = [
        'marketing_notifications_enabled' => isset($_POST['marketing_notifications_enabled']),
        'startup_notification' => [
            'enabled' => isset($_POST['startup_enabled']),
            'show_once_per_session' => isset($_POST['startup_show_once']),
            'title' => $_POST['startup_title'],
            'message' => $_POST['startup_message'],
            'url' => $_POST['startup_url'],
        ],
        'license_success_notification' => [
            'enabled' => isset($_POST['license_success_enabled']),
            'title' => $_POST['license_success_title'],
            'message' => $_POST['license_success_message'],
            'url' => $_POST['license_success_url'],
        ],
    ];

    if (file_put_contents($notifications_file, json_encode($new_settings, JSON_PRETTY_PRINT))) {
        $message = "Pengaturan notifikasi marketing berhasil diperbarui.";
        $settings = $new_settings;
    } else {
        $error = "Gagal menyimpan pengaturan. Pastikan folder 'downloads' dapat ditulis (writable).";
    }
}
?>
<style>
    .form-group { margin-bottom: 24px; }
    .form-group label { margin-bottom: 8px; font-weight: 500; color: #374151; display: block; }
    .form-group input[type="text"], .form-group textarea {
        width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 6px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-group input[type="text"]:focus, .form-group textarea:focus {
        border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); outline: none;
    }
    .form-switch { display: flex; align-items: center; gap: 12px; }
    .form-switch .label-text { margin-bottom: 0; }
    .toggle-switch { position: relative; display: inline-block; width: 50px; height: 28px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 28px; }
    .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: var(--primary-color); }
    input:checked + .slider:before { transform: translateX(22px); }
    .message { padding: 12px; background-color: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; border-radius: 6px; margin-bottom: 24px; }
    .error { padding: 12px; background-color: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; border-radius: 6px; margin-bottom: 24px; }
    .card-header { border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 24px; }
    .card-header h2 { margin:0; font-size: 1.25rem; }
</style>

<div class="header">
    <h1>Pengaturan Notifikasi Marketing</h1>
</div>

<?php if ($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>

<form action="marketing_settings.php" method="POST">
    <div class="card" style="margin-bottom: 32px;">
        <div class="form-switch">
            <label class="toggle-switch">
                <input type="checkbox" id="marketing_notifications_enabled" name="marketing_notifications_enabled" <?php echo ($settings['marketing_notifications_enabled'] ?? false) ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
            <label for="marketing_notifications_enabled" class="label-text"><b>Aktifkan Semua Notifikasi Marketing</b></label>
        </div>
    </div>

    <div class="card" style="margin-bottom: 32px;">
        <div class="card-header">
            <h2>Notifikasi Saat Aplikasi Dibuka</h2>
        </div>
        <div class="form-switch" style="margin-bottom: 24px;">
            <label class="toggle-switch">
                <input type="checkbox" id="startup_enabled" name="startup_enabled" <?php echo ($settings['startup_notification']['enabled'] ?? false) ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
            <label for="startup_enabled" class="label-text">Aktifkan notifikasi ini</label>
        </div>
        <div class="form-group">
            <label for="startup_title">Judul</label>
            <input type="text" id="startup_title" name="startup_title" value="<?php echo htmlspecialchars($settings['startup_notification']['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="startup_message">Pesan</label>
            <textarea id="startup_message" name="startup_message" rows="3"><?php echo htmlspecialchars($settings['startup_notification']['message'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="startup_url">URL (Opsional)</label>
            <input type="text" id="startup_url" name="startup_url" value="<?php echo htmlspecialchars($settings['startup_notification']['url'] ?? ''); ?>">
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Notifikasi Setelah Aktivasi Lisensi Sukses</h2>
        </div>
        <div class="form-switch" style="margin-bottom: 24px;">
            <label class="toggle-switch">
                <input type="checkbox" id="license_success_enabled" name="license_success_enabled" <?php echo ($settings['license_success_notification']['enabled'] ?? false) ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
            <label for="license_success_enabled" class="label-text">Aktifkan notifikasi ini</label>
        </div>
        <div class="form-group">
            <label for="license_success_title">Judul</label>
            <input type="text" id="license_success_title" name="license_success_title" value="<?php echo htmlspecialchars($settings['license_success_notification']['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="license_success_message">Pesan</label>
            <textarea id="license_success_message" name="license_success_message" rows="3"><?php echo htmlspecialchars($settings['license_success_notification']['message'] ?? ''); ?></textarea>
        </div>
         <div class="form-group">
            <label for="license_success_url">URL (Opsional)</label>
            <input type="text" id="license_success_url" name="license_success_url" value="<?php echo htmlspecialchars($settings['license_success_notification']['url'] ?? ''); ?>">
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary" style="margin-top: 32px; padding: 12px 24px; font-size: 1rem;">Simpan Pengaturan</button>
</form>

<?php require_once 'includes/footer.php'; ?>
