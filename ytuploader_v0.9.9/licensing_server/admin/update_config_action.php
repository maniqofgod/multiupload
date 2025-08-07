<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error_message_update'] = "Akses ditolak.";
    header('Location: dashboard.php');
    exit;
}

define('VERSION_CONFIG_PATH', __DIR__ . '/../config/version_info.json');

if (isset($_POST['save_update_config'])) {
    $latest_version = trim($_POST['latest_version'] ?? '');
    $changelog = trim($_POST['changelog'] ?? ''); // Biarkan \n untuk baris baru
    $download_url_windows = trim($_POST['download_url_windows'] ?? '');

    if (empty($latest_version) || empty($changelog) || empty($download_url_windows)) {
        $_SESSION['error_message_update'] = "Semua field konfigurasi update wajib diisi.";
        header('Location: dashboard.php');
        exit;
    }

    if (!filter_var($download_url_windows, FILTER_VALIDATE_URL)) {
         $_SESSION['error_message_update'] = "URL Download Windows tidak valid.";
        header('Location: dashboard.php');
        exit;
    }
    
    // Pattern sederhana untuk versi, bisa diperketat jika perlu
    if (!preg_match('/^\d+\.\d+\.\d+([a-zA-Z0-9.-]*)$/', $latest_version)) {
        $_SESSION['error_message_update'] = "Format Versi Terbaru tidak valid (contoh: 1.0.0, 0.9.1-beta).";
        header('Location: dashboard.php');
        exit;
    }


    $current_full_config = [];
    if (file_exists(VERSION_CONFIG_PATH)) {
        $json_content_current = file_get_contents(VERSION_CONFIG_PATH);
        $decoded_json_current = json_decode($json_content_current, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $current_full_config = $decoded_json_current;
        }
    }
    
    // Update atau buat struktur app_version_info
    $current_full_config['app_version_info']['latest_version'] = $latest_version;
    $current_full_config['app_version_info']['changelog'] = $changelog; // \n akan dipertahankan
    $current_full_config['app_version_info']['download_url_windows'] = $download_url_windows;
    // Tambahkan platform lain jika ada
    // $current_full_config['app_version_info']['download_url_linux'] = $_POST['download_url_linux'] ?? ''; 

    // Pastikan direktori config ada
    if (!is_dir(dirname(VERSION_CONFIG_PATH))) {
        mkdir(dirname(VERSION_CONFIG_PATH), 0755, true);
    }

    if (file_put_contents(VERSION_CONFIG_PATH, json_encode($current_full_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        $_SESSION['success_message_update'] = "Konfigurasi update berhasil disimpan.";
    } else {
        $_SESSION['error_message_update'] = "Gagal menyimpan konfigurasi update. Periksa izin tulis pada direktori config.";
    }
    header('Location: dashboard.php');
    exit;
} else {
    $_SESSION['error_message_update'] = "Aksi tidak valid.";
    header('Location: dashboard.php');
    exit;
}
?>