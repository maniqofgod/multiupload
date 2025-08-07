<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$config_path = __DIR__ . '/../config.php';

if (!file_exists($config_path)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "FATAL: File konfigurasi server (api/config.php) tidak ditemukan."]);
    exit;
}
require_once $config_path;

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Metode tidak diizinkan."]);
    exit;
}

if (!function_exists('mysqli_connect')) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "FATAL: Ekstensi PHP 'mysqli' tidak diaktifkan di server Anda."]);
    exit;
}

@$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Kesalahan koneksi database. Pastikan detail di api/config.php sudah benar. Pesan: " . $mysqli->connect_error
    ]);
    exit;
}

$settings = [];
$result = $mysqli->query("SELECT setting_key, setting_value FROM app_settings");

if ($result === false) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Query ke tabel 'app_settings' gagal. Apakah Anda sudah menjalankan setup.php? Error: " . $mysqli->error
    ]);
    $mysqli->close();
    exit;
}

if ($result->num_rows === 0) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Tabel 'app_settings' kosong atau tidak ada. Jalankan setup.php untuk mengisinya."
    ]);
    $mysqli->close();
    exit;
}

while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$mysqli->close();

$response_data = [
    "app_version_info" => [
        "latest_version" => $settings['latest_version'] ?? '1.0.0',
        "download_url_windows" => $settings['download_url'] ?? '',
        "release_notes_url" => $settings['release_notes_url'] ?? '',
        "is_mandatory" => (bool)($settings['is_mandatory_update'] ?? false),
        "update_message" => $settings['update_message'] ?? 'Versi baru tersedia!'
    ],
    "other_app_config" => [
        "maintenance_mode" => (bool)($settings['maintenance_mode'] ?? false),
        "maintenance_title" => "Mode Pemeliharaan",
        "maintenance_message" => $settings['maintenance_message'] ?? 'Aplikasi sedang dalam pemeliharaan.',
        "maintenance_end_time_placeholder" => ""
    ]
];

echo json_encode($response_data, JSON_PRETTY_PRINT);
?>
