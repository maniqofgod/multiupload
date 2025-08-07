<?php
header('Content-Type: application/json');

// File ini sengaja dibuat untuk kompatibilitas dengan panggilan lama.
// Sebaiknya, data ini digabungkan ke dalam check_version.php di masa depan.

$response_data = [
    "validation_message" => "Lisensi divalidasi.",
    "activation_message" => "Aktivasi berhasil."
];

echo json_encode($response_data, JSON_PRETTY_PRINT);
?>
