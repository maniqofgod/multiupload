<?php
// admin/update_license_action.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../api/license/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['edit_error_message'] = "Akses ditolak. Silakan login kembali.";
    $license_id_redirect = isset($_POST['license_id']) ? intval($_POST['license_id']) : 0;
    if ($license_id_redirect > 0) {
        header('Location: edit_license.php?id=' . $license_id_redirect);
    } else {
        header('Location: login.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $license_id = isset($_POST['license_id']) ? intval($_POST['license_id']) : 0;
    $license_key_display = $_POST['license_key_display'] ?? 'N/A'; 

    $_SESSION['edit_form_data'] = $_POST; 
    $_SESSION['edit_form_data']['id'] = $license_id;

    if ($license_id <= 0) {
        $_SESSION['error_message'] = "ID Lisensi tidak valid untuk update."; 
        unset($_SESSION['edit_form_data']);
        header('Location: dashboard.php');
        exit;
    }

    $email = !empty($_POST['email']) ? trim($_POST['email']) : null;
    $max_devices = isset($_POST['max_devices']) ? intval($_POST['max_devices']) : 0;
    $status_update = isset($_POST['status']) ? $_POST['status'] : 'pending';
    $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;
    $expires_at_input = !empty($_POST['expires_at']) ? trim($_POST['expires_at']) : null;
    $expires_at_db_update = null; 

    if ($max_devices < 0) $max_devices = 0;

    if ($expires_at_input) {
        $date_obj = DateTime::createFromFormat('Y-m-d', $expires_at_input);
        if ($date_obj) {
            $expires_at_db_update = $date_obj->format('Y-m-d H:i:s');
        } else {
            $_SESSION['edit_error_message'] = "Format tanggal kadaluwarsa tidak valid (gunakan YYYY-MM-DD).";
            header('Location: edit_license.php?id=' . $license_id);
            exit;
        }
    }

    $conn = getDbConnection();
    if (!$conn) {
        $_SESSION['edit_error_message'] = "Tidak bisa terhubung ke database untuk update.";
        header('Location: edit_license.php?id=' . $license_id);
        exit;
    }

    try {
        $stmt_update = $conn->prepare("UPDATE licenses SET email = ?, max_devices = ?, status = ?, expires_at = ?, notes = ? WHERE id = ?");
        if (!$stmt_update) {
             throw new Exception("Database prepare error (update): " . $conn->error);
        }
        $stmt_update->bind_param("sisssi", $email, $max_devices, $status_update, $expires_at_db_update, $notes, $license_id);

        if ($stmt_update->execute()) {
            $_SESSION['success_message'] = "Lisensi Key '" . htmlspecialchars($license_key_display) . "' (ID: " . $license_id . ") berhasil diupdate.";
            unset($_SESSION['edit_form_data']); 
            header('Location: dashboard.php?search='.urlencode($license_key_display)); 
        } else {
            throw new Exception("Gagal mengupdate lisensi: " . $stmt_update->error);
        }
        $stmt_update->close();

    } catch (Exception $e) {
        $_SESSION['edit_error_message'] = "Error mengupdate lisensi: " . $e->getMessage();
        header('Location: edit_license.php?id=' . $license_id);
    } finally {
        if (isset($conn) && $conn) {
            $conn->close();
        }
    }
    exit;

} else {
    header('Location: dashboard.php');
    exit;
}
?>