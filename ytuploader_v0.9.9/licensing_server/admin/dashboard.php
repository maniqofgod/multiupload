<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil statistik
$total_licenses = $mysqli->query("SELECT COUNT(*) FROM licenses")->fetch_row()[0];
$active_licenses = $mysqli->query("SELECT COUNT(*) FROM licenses WHERE status = 'active'")->fetch_row()[0];
$total_activations = $mysqli->query("SELECT COUNT(*) FROM activations")->fetch_row()[0];
$total_admins = $mysqli->query("SELECT COUNT(*) FROM admins")->fetch_row()[0];

$mysqli->close();
?>

<style>
.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; }
.stat-card { background-color: var(--card-bg); padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1); }
.stat-card h3 { margin: 0 0 8px 0; color: var(--text-light); font-size: 1rem; font-weight: 500; }
.stat-card p { margin: 0; font-size: 2.25rem; font-weight: 700; }
</style>

<div class="header">
    <h1>Dashboard</h1>
    <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <h3>Total Lisensi</h3>
        <p><?php echo $total_licenses; ?></p>
    </div>
    <div class="stat-card">
        <h3>Lisensi Aktif</h3>
        <p><?php echo $active_licenses; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Aktivasi</h3>
        <p><?php echo $total_activations; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Admin</h3>
        <p><?php echo $total_admins; ?></p>
    </div>
</div>

<div class="card" style="margin-top: 32px;">
    <h2>Selamat Datang di Panel Admin Profesional</h2>
    <p>Gunakan menu di sebelah kiri untuk menavigasi dan mengelola sistem lisensi Anda. Anda sekarang dapat mengelola pengguna admin lain dari menu "Kelola Admin".</p>
</div>

<?php require_once 'includes/footer.php'; ?>
