<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once '../api/config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        die("Koneksi database gagal: " . $mysqli->connect_error);
    }

    $stmt = $mysqli->prepare("DELETE FROM licenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

header('Location: manage_licenses.php');
exit;
?>
