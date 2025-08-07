<?php
header('Content-Type: application/json');
require_once '../config.php'; // Menggunakan config utama

// Fungsi Bantuan
function send_response($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Koneksi Database
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    send_response(['status' => 'error', 'message' => 'Kesalahan koneksi server.'], 500);
}

// Ambil input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);
$action = $input['action'] ?? $_POST['action'] ?? null;
$license_key = trim($input['license_key'] ?? $_POST['license_key'] ?? '');
$hwid = trim($input['hwid'] ?? $_POST['hwid'] ?? '');

if (empty($action) || empty($license_key) || empty($hwid)) {
    send_response(['status' => 'error', 'message' => 'Input tidak lengkap: action, license_key, dan hwid diperlukan.'], 400);
}

// --- Logika Utama ---

// Ambil data lisensi
$stmt = $mysqli->prepare("SELECT id, status, expiry_date, max_devices FROM licenses WHERE license_key = ?");
$stmt->bind_param("s", $license_key);
$stmt->execute();
$license_result = $stmt->get_result();

if ($license_result->num_rows === 0) {
    send_response(['status' => 'error', 'message' => 'Kunci lisensi tidak ditemukan.'], 404);
}
$license = $license_result->fetch_assoc();
$stmt->close();

// Periksa status dan tanggal kedaluwarsa
if ($license['status'] !== 'active') {
    send_response(['status' => 'error', 'message' => 'Status lisensi: ' . $license['status'] . '.'], 403);
}
if ($license['expiry_date'] && strtotime($license['expiry_date']) < time()) {
    send_response(['status' => 'error', 'message' => 'Lisensi telah kedaluwarsa.'], 403);
}

switch ($action) {
    case 'validate':
        $stmt = $mysqli->prepare("SELECT id FROM activations WHERE license_id = ? AND hwid = ?");
        $stmt->bind_param("is", $license['id'], $hwid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            $updateStmt = $mysqli->prepare("UPDATE activations SET last_seen_at = NOW() WHERE license_id = ? AND hwid = ?");
            $updateStmt->bind_param("is", $license['id'], $hwid);
            $updateStmt->execute();
            $updateStmt->close();
            send_response(['status' => 'success', 'message' => 'Lisensi valid.']);
        } else {
            send_response(['status' => 'error', 'message' => 'Perangkat ini tidak terdaftar untuk lisensi tersebut.'], 403);
        }
        break;

    case 'activate':
        // Hitung jumlah aktivasi saat ini
        $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM activations WHERE license_id = ?");
        $stmt->bind_param("i", $license['id']);
        $stmt->execute();
        $activation_count = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();

        // Cek apakah perangkat ini sudah teraktivasi
        $stmt = $mysqli->prepare("SELECT id FROM activations WHERE license_id = ? AND hwid = ?");
        $stmt->bind_param("is", $license['id'], $hwid);
        $stmt->execute();
        $is_already_activated = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($is_already_activated) {
            send_response(['status' => 'success', 'message' => 'Perangkat sudah teraktivasi sebelumnya.']);
        } elseif ($activation_count < $license['max_devices']) {
            $insert_stmt = $mysqli->prepare("INSERT INTO activations (license_id, hwid) VALUES (?, ?)");
            $insert_stmt->bind_param("is", $license['id'], $hwid);
            if ($insert_stmt->execute()) {
                send_response(['status' => 'success', 'message' => 'Aktivasi berhasil.']);
            } else {
                send_response(['status' => 'error', 'message' => 'Gagal menyimpan aktivasi.'], 500);
            }
            $insert_stmt->close();
        } else {
            send_response(['status' => 'error', 'message' => 'Jumlah maksimum perangkat untuk lisensi ini telah tercapai.'], 403);
        }
        break;

    default:
        send_response(['status' => 'error', 'message' => 'Aksi tidak valid.'], 400);
        break;
}

$mysqli->close();
?>
