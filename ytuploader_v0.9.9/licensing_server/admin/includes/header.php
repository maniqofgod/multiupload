<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}
require_once '../api/config.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4F46E5;
            --sidebar-bg: #1F2937;
            --body-bg: #F3F4F6;
            --card-bg: #FFFFFF;
            --text-color: #374151;
            --text-light: #6B7280;
            --border-color: #E5E7EB;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--body-bg); margin: 0; display: flex; min-height: 100vh; color: var(--text-color); }
        .sidebar { width: 250px; background-color: var(--sidebar-bg); color: white; padding: 24px; flex-shrink: 0; display: flex; flex-direction: column; }
        .sidebar-header { text-align: center; margin-bottom: 40px; }
        .sidebar-header h2 { margin: 0; font-size: 1.5rem; }
        .sidebar nav a { color: #D1D5DB; text-decoration: none; padding: 12px 16px; display: block; border-radius: 6px; margin-bottom: 8px; font-weight: 500; transition: background-color 0.2s, color 0.2s; }
        .sidebar nav a:hover { background-color: #374151; color: white; }
        .sidebar nav a.active { background-color: var(--primary-color); color: white; }
        .sidebar .logout { margin-top: auto; }
        .main-content { flex-grow: 1; padding: 32px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .header h1 { margin: 0; font-size: 1.875rem; }
        .card { background-color: var(--card-bg); padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px 0 rgba(0,0,0,0.06); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 16px; border-bottom: 1px solid var(--border-color); text-align: left; }
        th { background-color: #F9FAFB; font-weight: 600; color: var(--text-light); }
        .btn { padding: 8px 16px; border-radius: 6px; text-decoration: none; color: white; border: none; cursor: pointer; font-weight: 500; transition: background-color 0.2s; }
        .btn-primary { background-color: var(--primary-color); }
        .btn-primary:hover { background-color: #4338CA; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
        </div>
        <nav>
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
            <a href="manage_licenses.php" class="<?php echo ($current_page == 'manage_licenses.php' || $current_page == 'edit_license.php' || $current_page == 'view_activations.php') ? 'active' : ''; ?>">Kelola Lisensi</a>
            <a href="app_settings.php" class="<?php echo ($current_page == 'app_settings.php') ? 'active' : ''; ?>">Pengaturan Aplikasi</a>
            <a href="marketing_settings.php" class="<?php echo ($current_page == 'marketing_settings.php') ? 'active' : ''; ?>">Notifikasi Marketing</a>
            <a href="manage_secrets.php" class="<?php echo ($current_page == 'manage_secrets.php') ? 'active' : ''; ?>">Kelola Client Secret</a>
            <a href="manage_admins.php" class="<?php echo ($current_page == 'manage_admins.php') ? 'active' : ''; ?>">Kelola Admin</a>
            <a href="change_password.php" class="<?php echo ($current_page == 'change_password.php') ? 'active' : ''; ?>">Ganti Password</a>
        </nav>
        <a href="logout.php" class="logout">Logout</a>
    </div>
    <div class="main-content">
