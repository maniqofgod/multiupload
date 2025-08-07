<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once '../api/config.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $activation_id = (int)$_GET['id'];

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        die("Koneksi database gagal: " . $mysqli->connect_error);
    }

    // Ambil license_id sebelum menghapus untuk redirect kembali
    $stmt_get_id = $mysqli->prepare("SELECT license_id FROM activations WHERE id = ?");
    $stmt_get_id->bind_param("i", $activation_id);
    $stmt_get_id->execute();
    $result = $stmt_get_id->get_result();
    $license_id = ($result->num_rows > 0) ? $result->fetch_assoc()['license_id'] : null;
    $stmt_get_id->close();

    // Hapus aktivasi
    $stmt_delete = $mysqli->prepare("DELETE FROM activations WHERE id = ?");
    $stmt_delete->bind_param("i", $activation_id);
    $stmt_delete->execute();
    $stmt_delete->close();
    $mysqli->close();

    if ($license_id) {
        header('Location: view_activations.php?license_id=' . $license_id);
    } else {
        header('Location: manage_licenses.php');
    }
    exit;
} else {
    header('Location: manage_licenses.php');
    exit;
}
?>
