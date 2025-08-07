<?php
$page_title = 'Kelola Client Secrets';
require_once 'includes/header.php';

// Security check for superadmin
$mysqli_check = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$username_check = $_SESSION['admin_username'];
$stmt_role_check = $mysqli_check->prepare("SELECT role FROM admins WHERE username = ?");
$stmt_role_check->bind_param("s", $username_check);
$stmt_role_check->execute();
$user_role = $stmt_role_check->get_result()->fetch_assoc()['role'];
$stmt_role_check->close();
$mysqli_check->close();
if ($user_role !== 'superadmin') {
    echo "<div class='header'><h1>Akses Ditolak</h1></div><div class='card'><p>Anda tidak memiliki izin untuk mengakses halaman ini.</p></div>";
    require_once 'includes/footer.php';
    exit;
}


$secrets_dir = '../secrets/';
$index_file = $secrets_dir . 'index.json';
$message = '';
$error = '';

function read_secrets_index($file_path) {
    if (!file_exists($file_path)) return [];
    return json_decode(file_get_contents($file_path), true) ?? [];
}

function save_secrets_index($file_path, $data) {
    return file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT));
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secrets = read_secrets_index($index_file);

    // Add/Upload new secret
    if (isset($_POST['add_secret'])) {
        $display_name = trim($_POST['display_name']);
        if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === 0 && !empty($display_name)) {
            $file_name = basename($_FILES["json_file"]["name"]);
            $target_file = $secrets_dir . $file_name;
            if (strtolower(pathinfo($target_file, PATHINFO_EXTENSION)) !== 'json') {
                $error = "Hanya file .json yang diizinkan.";
            } elseif (file_exists($target_file)) {
                $error = "File dengan nama '$file_name' sudah ada.";
            } else {
                if (move_uploaded_file($_FILES["json_file"]["tmp_name"], $target_file)) {
                    $secrets[] = ['display_name' => $display_name, 'filename' => $file_name];
                    if (save_secrets_index($index_file, $secrets)) {
                        $message = "Client Secret '$display_name' berhasil ditambahkan.";
                    } else {
                        $error = "Gagal menyimpan ke index.json.";
                        unlink($target_file); // Rollback upload
                    }
                } else {
                    $error = "Gagal mengunggah file.";
                }
            }
        } else {
            $error = "Nama Tampilan dan File JSON harus diisi.";
        }
    }

    // Edit secret display name
    if (isset($_POST['edit_secret'])) {
        $filename_to_edit = $_POST['filename'];
        $new_display_name = trim($_POST['new_display_name']);
        $found = false;
        foreach ($secrets as $i => $secret) {
            if ($secret['filename'] === $filename_to_edit) {
                $secrets[$i]['display_name'] = $new_display_name;
                $found = true;
                break;
            }
        }
        if ($found && save_secrets_index($index_file, $secrets)) {
            $message = "Nama tampilan berhasil diperbarui.";
        } else {
            $error = "Gagal memperbarui nama tampilan.";
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $filename_to_delete = $_GET['delete'];
    $secrets = read_secrets_index($index_file);
    $file_path = realpath($secrets_dir . $filename_to_delete);
    
    if ($file_path && file_exists($file_path)) {
        unlink($file_path);
        $secrets = array_filter($secrets, function($secret) use ($filename_to_delete) {
            return $secret['filename'] !== $filename_to_delete;
        });
        save_secrets_index($index_file, array_values($secrets));
        $message = "Client Secret '$filename_to_delete' berhasil dihapus.";
    } else {
        $error = "File tidak ditemukan atau aksi tidak valid.";
    }
    header('Location: manage_secrets.php?message=' . urlencode($message) . '&error=' . urlencode($error));
    exit;
}

$secrets = read_secrets_index($index_file);
?>
<style>
    .form-group { margin-bottom: 16px; }
    .form-group label { margin-bottom: 8px; font-weight: 500; display: block; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 6px; box-sizing: border-box; }
    .btn-delete { color: #EF4444; text-decoration: none; font-weight: 500; }
    .btn-edit { color: #F59E0B; text-decoration: none; font-weight: 500; margin-right: 16px; }
    .message { padding: 12px; background-color: #D1FAE5; color: #065F46; border-radius: 6px; margin-bottom: 24px; }
    .error { padding: 12px; background-color: #FEE2E2; color: #991B1B; border-radius: 6px; margin-bottom: 24px; }
</style>

<div class="header">
    <h1>Kelola Client Secrets</h1>
</div>

<?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="card" style="margin-bottom: 32px;">
    <h2>Tambah Client Secret Baru</h2>
    <form action="manage_secrets.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="add_secret" value="1">
        <div class="form-group">
            <label for="display_name">Nama Tampilan (Akan muncul di aplikasi)</label>
            <input type="text" id="display_name" name="display_name" required>
        </div>
        <div class="form-group">
            <label for="json_file">File client_secret.json</label>
            <input type="file" name="json_file" id="json_file" accept=".json" required>
        </div>
        <button type="submit" class="btn btn-primary">Tambah & Unggah</button>
    </form>
</div>

<div class="card">
    <h2>Daftar Client Secret</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Tampilan</th>
                <th>Nama File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($secrets)): ?>
                <tr><td colspan="3" style="text-align: center;">Belum ada client secret.</td></tr>
            <?php else: ?>
                <?php foreach ($secrets as $secret): ?>
                <tr>
                    <td><?php echo htmlspecialchars($secret['display_name']); ?></td>
                    <td><code><?php echo htmlspecialchars($secret['filename']); ?></code></td>
                    <td>
                        <a href="#" class="btn-edit" onclick="editSecret('<?php echo $secret['filename']; ?>', '<?php echo htmlspecialchars($secret['display_name']); ?>')">Edit Nama</a>
                        <a href="manage_secrets.php?delete=<?php echo urlencode($secret['filename']); ?>" class="btn-delete" onclick="return confirm('Anda yakin ingin menghapus secret ini? File fisik juga akan dihapus.');">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<form id="editForm" action="manage_secrets.php" method="POST" style="display: none;">
    <input type="hidden" name="edit_secret" value="1">
    <input type="hidden" name="filename" id="edit_filename">
    <input type="hidden" name="new_display_name" id="edit_new_display_name">
</form>

<script>
function editSecret(filename, currentDisplayName) {
    const newName = prompt("Masukkan Nama Tampilan baru untuk " + filename + ":", currentDisplayName);
    if (newName && newName !== currentDisplayName) {
        document.getElementById('edit_filename').value = filename;
        document.getElementById('edit_new_display_name').value = newName;
        document.getElementById('editForm').submit();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
