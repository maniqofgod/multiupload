<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error_message_update'] = "Akses ditolak.";
    header('Location: dashboard.php');
    exit;
}

define('APP_DOWNLOADS_DIR', __DIR__ . '/../downloads/');

if (isset($_POST['upload_app_file'])) {
    if (isset($_FILES['app_file']) && $_FILES['app_file']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['app_file']['tmp_name'];
        $file_name = basename($_FILES['app_file']['name']); // basename untuk keamanan
        $file_size = $_FILES['app_file']['size'];
        $file_type = $_FILES['app_file']['type'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = ['exe', 'zip'];
        $max_file_size = 500 * 1024 * 1024; // 500 MB, sesuaikan jika perlu

        if (!in_array($file_extension, $allowed_extensions)) {
            $_SESSION['error_message_update'] = "Tipe file tidak diizinkan. Hanya .exe dan .zip yang boleh diupload.";
        } elseif ($file_size > $max_file_size) {
            $_SESSION['error_message_update'] = "Ukuran file terlalu besar. Maksimum " . ($max_file_size / 1024 / 1024) . " MB.";
        } else {
            // Pastikan direktori downloads ada dan bisa ditulis
            if (!is_dir(APP_DOWNLOADS_DIR)) {
                if (!mkdir(APP_DOWNLOADS_DIR, 0755, true)) {
                    $_SESSION['error_message_update'] = "Gagal membuat direktori downloads. Periksa izin server.";
                    header('Location: dashboard.php');
                    exit;
                }
            }
            if (!is_writable(APP_DOWNLOADS_DIR)){
                 $_SESSION['error_message_update'] = "Direktori downloads tidak dapat ditulis. Periksa izin server.";
                 header('Location: dashboard.php');
                 exit;
            }

            $destination_path = APP_DOWNLOADS_DIR . $file_name;

            if (move_uploaded_file($file_tmp_path, $destination_path)) {
                $download_url_base = rtrim(dirname(dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'))), '/') . '/downloads/';
                $full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $download_url_base . rawurlencode($file_name);
                $_SESSION['success_message_update'] = "File '" . htmlspecialchars($file_name) . "' berhasil diupload. URL: " . htmlspecialchars($full_url);
            } else {
                $_SESSION['error_message_update'] = "Gagal memindahkan file yang diupload. Error: " . $_FILES['app_file']['error'];
            }
        }
    } else {
        $_SESSION['error_message_update'] = "Tidak ada file yang dipilih atau terjadi error saat upload. Error code: " . ($_FILES['app_file']['error'] ?? 'N/A');
    }
    header('Location: dashboard.php');
    exit;
} else {
    $_SESSION['error_message_update'] = "Aksi tidak valid.";
    header('Location: dashboard.php');
    exit;
}
?>