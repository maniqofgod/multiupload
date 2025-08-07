<?php
session_start();
require_once '../api/config.php'; // Menggunakan config API utama

// Jika sudah login, arahkan ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password tidak boleh kosong.';
    } else {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($mysqli->connect_error) {
            $error_message = 'Koneksi database gagal.';
        } else {
            $stmt = $mysqli->prepare("SELECT password_hash FROM admins WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password_hash'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $username;
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error_message = 'Password salah.';
                }
            } else {
                $error_message = 'Username tidak ditemukan.';
            }
            $stmt->close();
            $mysqli->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #4F46E5; --body-bg: #F3F4F6; }
        body { font-family: 'Inter', sans-serif; background-color: var(--body-bg); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background-color: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 380px; }
        h1 { text-align: center; color: #1F2937; margin-bottom: 32px; font-weight: 700; }
        .form-group { margin-bottom: 24px; }
        label { display: block; margin-bottom: 8px; color: #4B5563; font-weight: 500; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; }
        input[type="text"]:focus, input[type="password"]:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2); outline: none; }
        button { width: 100%; padding: 12px; background-color: var(--primary-color); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600; transition: background-color 0.2s; }
        button:hover { background-color: #4338CA; }
        .error { color: #EF4444; text-align: center; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Panel Login</h1>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <?php if (!empty($error_message)): ?>
                <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
