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

if (isset($_GET['file'])) {
    $file_to_delete = basename($_GET['file']); // basename untuk keamanan
    $file_path = APP_DOWNLOADS_DIR . $file_to_delete;

    if (file_exists($file_path) && (strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'exe' || strtolower(pathinfo($file_path, PATHINFO_EXTENSION)) === 'zip')) {
        if (unlink($file_path)) {
            $_SESSION['success_message_update'] = "File '" . htmlspecialchars($file_to_delete) . "' berhasil dihapus.";
        } else {
            $_SESSION['error_message_update'] = "Gagal menghapus file '" . htmlspecialchars($file_to_delete) . "'. Periksa izin.";
        }
    } else {
        $_SESSION['error_message_update'] = "File '" . htmlspecialchars($file_to_delete) . "' tidak ditemukan atau tipe tidak valid.";
    }
} else {
    $_SESSION['error_message_update'] = "Nama file tidak diberikan untuk dihapus.";
}
header('Location: dashboard.php');
exit;
?>