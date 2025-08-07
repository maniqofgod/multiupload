<?php
// admin/download_licenses.php
ini_set('display_errors', 0); // Jangan tampilkan error di file CSV
error_reporting(0);

session_start();
require_once '../api/license/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Seharusnya tidak bisa diakses langsung, tapi sebagai pengaman
    header('HTTP/1.1 403 Forbidden');
    exit("Akses ditolak.");
}

$conn = getDbConnection();
if (!$conn) {
    header('HTTP/1.1 500 Internal Server Error');
    exit("Koneksi database gagal.");
}

$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT l.id, l.license_key, l.email, l.max_devices, l.status, l.created_at, l.expires_at, l.notes,
               GROUP_CONCAT(DISTINCT act.hwid SEPARATOR '; ') as activated_hwids_csv 
               -- Gunakan separator berbeda untuk CSV jika <br> tidak diinginkan
        FROM licenses l
        LEFT JOIN activations act ON l.id = act.license_id";

$params = [];
$types = "";
$where_clauses = [];

if (!empty($search_term)) {
    $term_like = "%" . $search_term . "%";
    $where_clauses[] = "(l.license_key LIKE ? OR l.email LIKE ? OR l.notes LIKE ? OR act.hwid LIKE ?)";
    array_push($params, $term_like, $term_like, $term_like, $term_like);
    $types .= "ssss";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " GROUP BY l.id ORDER BY l.created_at DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $filename = "licenses_export_" . date('Y-m-d_H-i-s') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, ['ID', 'License Key', 'Email', 'Max Devices', 'Status', 'Created At', 'Expires At', 'Activated HWIDs', 'Notes']);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['license_key'],
                $row['email'],
                $row['max_devices'],
                $row['status'],
                $row['created_at'],
                $row['expires_at'],
                $row['activated_hwids_csv'], // Gunakan alias baru
                $row['notes']
            ]);
        }
        fclose($output);
        $stmt->close();
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        exit("Gagal mengambil data untuk CSV: " . $stmt->error);
    }
} else {
    header('HTTP/1.1 500 Internal Server Error');
    exit("Gagal mempersiapkan query CSV: " . $conn->error);
}
$conn->close();
exit;
?>