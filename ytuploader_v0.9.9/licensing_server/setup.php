<?php
require_once 'api/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "--- Skrip Setup Database & Panel Admin v2.0 ---\n\n";

// Langkah 1: Koneksi ke MySQL Server
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($mysqli->connect_error) {
    die("GAGAL: Tidak dapat terhubung ke server MySQL di '" . DB_HOST . "'.\nError: " . $mysqli->connect_error . "\n\nPastikan detail di api/config.php sudah benar.");
}
echo "Sukses terhubung ke server MySQL.\n";

// Langkah 2: Pilih atau Buat Database
if (!$mysqli->select_db(DB_NAME)) {
    echo "Database '" . DB_NAME . "' tidak ditemukan. Mencoba membuatnya...\n";
    $sql_create_db = "CREATE DATABASE " . DB_NAME;
    if ($mysqli->query($sql_create_db) === TRUE) {
        echo "Database '" . DB_NAME . "' berhasil dibuat.\n";
        $mysqli->select_db(DB_NAME);
    } else {
        die("GAGAL: Tidak dapat membuat database '" . DB_NAME . "'.\nError: " . $mysqli->error . "\n\nHarap buat database secara manual melalui panel kontrol hosting Anda.");
    }
}
echo "Sukses terhubung ke database '" . DB_NAME . "'.\n\n";

// Langkah 3: Buat Tabel 'licenses'
$sql_licenses = "
CREATE TABLE IF NOT EXISTS licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('active', 'expired', 'disabled', 'pending') NOT NULL DEFAULT 'pending',
    expiry_date DATE NULL,
    customer_name VARCHAR(255) NULL,
    notes TEXT NULL,
    max_devices INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status)
);";
echo "Membuat tabel 'licenses'...\n";
if ($mysqli->query($sql_licenses) === TRUE) {
    echo " -> Sukses! Tabel 'licenses' siap.\n";
} else {
    die("GAGAL membuat tabel 'licenses': " . $mysqli->error . "\n");
}

// Langkah 4: Buat Tabel 'activations'
$sql_activations = "
CREATE TABLE IF NOT EXISTS activations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_id INT NOT NULL,
    hwid VARCHAR(255) NOT NULL,
    activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
    UNIQUE KEY (license_id, hwid)
);";
echo "Membuat tabel 'activations'...\n";
if ($mysqli->query($sql_activations) === TRUE) {
    echo " -> Sukses! Tabel 'activations' siap.\n";
} else {
    die("GAGAL membuat tabel 'activations': " . $mysqli->error . "\n");
}


// Langkah 5: Buat Tabel 'admins' dengan kolom 'role'
$sql_admins = "
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
echo "Membuat tabel 'admins'...\n";
if ($mysqli->query($sql_admins) === TRUE) {
    echo " -> Sukses! Tabel 'admins' siap.\n";
    if ($mysqli->query("SELECT id FROM admins LIMIT 1")->num_rows == 0) {
        echo " -> Tabel 'admins' kosong. Menambahkan superadmin default...\n";
        $default_user = 'admin';
        $default_pass = 'admin123';
        $password_hash = password_hash($default_pass, PASSWORD_DEFAULT);
        $default_role = 'superadmin';
        $insert_admin_stmt = $mysqli->prepare("INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)");
        $insert_admin_stmt->bind_param("sss", $default_user, $password_hash, $default_role);
        if ($insert_admin_stmt->execute()) {
            echo " -> Superadmin default berhasil dibuat.\n";
            echo "    Username: " . $default_user . "\n";
            echo "    Password: " . $default_pass . "\n";
        } else {
            echo " -> GAGAL menambahkan superadmin default: " . $insert_admin_stmt->error . "\n";
        }
        $insert_admin_stmt->close();
    }
} else {
    die("GAGAL membuat tabel 'admins': " . $mysqli->error . "\n");
}

// Langkah 6: Buat Tabel 'app_settings'
$sql_settings = "
CREATE TABLE IF NOT EXISTS app_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NULL
);";
echo "Membuat tabel 'app_settings'...\n";
if ($mysqli->query($sql_settings) === TRUE) {
    echo " -> Sukses! Tabel 'app_settings' siap.\n";
    if ($mysqli->query("SELECT setting_key FROM app_settings LIMIT 1")->num_rows == 0) {
        echo " -> Tabel 'app_settings' kosong. Mengisi dengan data default...\n";
        $default_settings = [
            'latest_version' => '1.0.0',
            'download_url' => 'https://example.com/download/app.zip',
            'release_notes_url' => 'https://example.com/notes.html',
            'is_mandatory_update' => '0',
            'update_message' => 'Versi baru {version} tersedia!',
            'maintenance_mode' => '0',
            'maintenance_message' => 'Server sedang dalam pemeliharaan.'
        ];
        $insert_setting_stmt = $mysqli->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($default_settings as $key => $value) {
            $insert_setting_stmt->bind_param("ss", $key, $value);
            $insert_setting_stmt->execute();
        }
        echo " -> Pengaturan default berhasil dimasukkan.\n";
        $insert_setting_stmt->close();
    }
} else {
    die("GAGAL membuat tabel 'app_settings': " . $mysqli->error . "\n");
}

echo "\n--- SETUP DATABASE SELESAI ---\n\n";
echo "PENTING: Untuk alasan keamanan, segera hapus file 'setup.php' ini dari server Anda!\n";

$mysqli->close();
?>
