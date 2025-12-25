<?php

require 'auth.php';

// Koneksi database
$conn = new mysqli('localhost', 'root', '', 'laundry_stecu');
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Semua field wajib diisi!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password baru minimal 6 karakter!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password baru dan konfirmasi tidak cocok!";
    } else {

        // Ambil password lama dari DB
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($db_password);
        $stmt->fetch();
        $stmt->close();

        // Verifikasi password lama
        if (!password_verify($old_password, $db_password)) {
            $error = "Password lama salah!";
        }

        // 2️⃣ CEK PASSWORD BARU TIDAK BOLEH SAMA
        elseif (password_verify($new_password, $db_password)) {
            $error = "Password baru tidak boleh sama dengan password lama!";
        }
        
        else {

            // Hash password baru
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_hashed, $user_id);

            if ($stmt->execute()) {

                // 🔒 PAKSA LOGOUT (DISARANKAN)
                session_destroy();
                header("Location: login.php?password=changed");
                exit;

            } else {
                $error = "Gagal mengubah password!";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Pelanggan</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        /* ===== CONTENT ===== */
        .container {
            max-width: 600px;
            margin: 25px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* tombol kembali di atas */
        .top-bar {
            margin-bottom: 15px;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 14px;
            background: #2c3e50;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-back:hover {
            background: #1abc9c;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
        }

        input[type="password"],
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        textarea {
            height: 80px;
            resize: none;
        }

        .btn-group {
            margin-top: 20px;
            text-align: right;
        }

        button[type="submit"] {
            background: #1abc9c;
            border: none;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button[type="submit"]:hover {
            background: #16a085;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 70%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
        }

        .input-wrapper {
            position: relative;
        }
    </style>
</head>

<body>

    <!-- ===== CONTENT ===== -->
    <div class="container">

        <!-- TOMBOL KEMBALI -->
        <div class="top-bar">
            <a href="dashboard.php" class="btn-back">← Kembali</a>
        </div>

        <h2>Ganti Password</h2>

        <!-- ALERT ERROR -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- ALERT SUCCESS (jika tidak redirect) -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="input-wrapper">
                <label>Password Lama</label>
                <input type="password" name="old_password" id="password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('password')">
                    👁️
                </button>
            </div>

            <br><br>

            <div class="input-wrapper">
                <label>Password Baru</label>
                <input type="password" name="new_password" id="new_password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                    👁️
                </button>
            </div>

            <br><br>

            <div class="input-wrapper">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                    👁️
                </button>
            </div>

            <div class="btn-group">
                <button type="submit">Simpan</button>
            </div>

        </form>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        }
    </script>

</body>

</html>