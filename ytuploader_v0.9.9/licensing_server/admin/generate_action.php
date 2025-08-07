<?php
// admin/generate_action.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../api/license/db_connect.php'; 

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$_SESSION['form_data'] = $_POST; 

function generate_unique_key_php_from_action_debug($conn_param) {
    $prefix = "SOLUAI-"; 
    $max_attempts = 100;
    $attempt = 0;
    $key = ''; 

    if (!$conn_param) {
        throw new Exception("Koneksi database tidak valid saat generate kunci.");
    }

    do {
        $random_part1 = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $random_part2 = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $random_part3 = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $key = $prefix . $random_part1 . "-" . $random_part2 . "-" . $random_part3;
        $attempt++;

        $stmt_check = $conn_param->prepare("SELECT id FROM licenses WHERE license_key = ?");
        if (!$stmt_check) {
            throw new Exception("Database prepare error (cek kunci): " . $conn_param->error);
        }
        $stmt_check->bind_param("s", $key);
        if (!$stmt_check->execute()) {
            $stmt_check->close();
            throw new Exception("Database execute error (cek kunci): " . $stmt_check->error);
        }
        
        $result_check = $stmt_check->get_result();
        $exists = $result_check->fetch_assoc();
        $stmt_check->close();

        if ($attempt > $max_attempts) {
            throw new Exception("Gagal membuat kunci unik setelah " . $max_attempts . " percobaan.");
        }
    } while ($exists);
    return $key;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $conn = getDbConnection(); 
    if (!$conn) {
        $_SESSION['error_message'] = "Tidak bisa terhubung ke database.";
        header('Location: dashboard.php');
        exit;
    }
    
    $generated_key_for_log = 'N/A (belum tergenerate)'; 

    try {
        $new_key = generate_unique_key_php_from_action_debug($conn);
        $generated_key_for_log = $new_key; 

        $max_devices = isset($_POST['max_devices']) ? intval($_POST['max_devices']) : 2;
        if ($max_devices < 1) $max_devices = 1;

        $status = isset($_POST['status']) && in_array($_POST['status'], ['pending', 'active']) ? $_POST['status'] : 'pending';
        
        $email_input = $_POST['email'] ?? '';
        $email = !empty(trim($email_input)) ? trim($email_input) : null;

        $notes_input = $_POST['notes'] ?? '';
        $notes = !empty(trim($notes_input)) ? trim($notes_input) : null;

        $expires_at_input = $_POST['expires_at'] ?? '';
        $expires_at_db = null;
        if (!empty(trim($expires_at_input))) {
            $date_obj = DateTime::createFromFormat('Y-m-d', trim($expires_at_input));
            if ($date_obj) {
                $expires_at_db = $date_obj->format('Y-m-d H:i:s');
            } else {
                throw new Exception("Format tanggal kadaluwarsa tidak valid (gunakan YYYY-MM-DD).");
            }
        }

        $stmt_insert = $conn->prepare("INSERT INTO licenses (license_key, max_devices, status, email, notes, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt_insert) {
            throw new Exception("Database prepare error (insert lisensi): " . $conn->error);
        }
        
        $stmt_insert->bind_param("sissss", $new_key, $max_devices, $status, $email, $notes, $expires_at_db);
        
        if ($stmt_insert->execute()) {
            $_SESSION['new_key_generated'] = $new_key;
            unset($_SESSION['form_data']); 
        } else {
            throw new Exception("Gagal menyimpan kunci ke database: " . $stmt_insert->error);
        }
        $stmt_insert->close();

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error saat generate: " . $e->getMessage();
    } finally {
        if (isset($conn) && $conn) {
            $conn->close();
        }
    }
} else {
    $_SESSION['error_message'] = "Aksi tidak valid.";
}
header('Location: dashboard.php');
exit;
?>