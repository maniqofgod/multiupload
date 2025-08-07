<?php
// api/license/db_connect.php
require_once 'config.php';

function getDbConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        // Jangan tampilkan error detail ke klien di produksi
        // Log error ke file di server
        error_log("Koneksi database gagal: " . $conn->connect_error);
        // Kirim respons error generik
        header('Content-Type: application/json');
        http_response_code(500); // Internal Server Error
        echo json_encode(["status" => "error", "message" => "Kesalahan internal server (DB Connection)."]);
        exit; // Hentikan eksekusi skrip lebih lanjut
    }
    return $conn;
}
?>