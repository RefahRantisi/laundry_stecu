<?php
require 'auth_owner.php';
include 'koneksi.php';

$cabang_id = (int) $_GET['cabang_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm)) {
        $error = "Semua field wajib diisi";
    } elseif (strlen($username) < 4) {
        $error = "Username minimal 4 karakter";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter";
    } elseif ($password !== $confirm) {
        $error = "Password dan konfirmasi tidak cocok";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username sudah digunakan";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users 
                (username, password, role, owner_id, cabang_id, is_active)
                VALUES (?, ?, 'admin', ?, ?, 1)
            ");
            $stmt->bind_param(
                "ssii",
                $username,
                $hash,
                $_SESSION['owner_id'],
                $cabang_id
            );
            $stmt->execute();

            header("Location: cabang_detail.php?id=$cabang_id");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Admin</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:Arial}
        body{
            background:#f4f4f4;
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }
        .modal{
            background:#fff;
            width:100%;
            max-width:450px;
            padding:30px;
            border-radius:12px;
            box-shadow:0 10px 40px rgba(0,0,0,.2);
        }
        h2{text-align:center;margin-bottom:5px}
        p{text-align:center;color:#777;margin-bottom:20px}
        .alert{
            background:#fee;
            color:#c33;
            padding:10px;
            border-radius:6px;
            margin-bottom:15px;
            font-size:14px;
        }
        label{font-weight:bold;font-size:14px}
        input{
            width:100%;
            padding:12px;
            margin:8px 0 15px;
            border-radius:8px;
            border:2px solid #ddd;
        }
        input:focus{border-color:#2c3e50;outline:none}
        button{
            width:100%;
            padding:14px;
            background:#2c3e50;
            color:white;
            border:none;
            border-radius:8px;
            font-weight:bold;
            cursor:pointer;
        }
        button:hover{background:#34495e}
        .back{
            display:block;
            text-align:center;
            margin-top:15px;
            text-decoration:none;
            color:#555;
            font-size:14px;
        }
    </style>
</head>

<body>

<div class="modal">
    <h2>Tambah Admin</h2>
    <p>Admin khusus untuk cabang ini</p>

    <?php if ($error): ?>
        <div class="alert"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="confirm_password" required>

        <button>Simpan Admin</button>
    </form>

    <a href="cabang_detail.php?id=<?= $cabang_id ?>" class="back">
        ← Kembali ke Detail Cabang
    </a>
</div>

</body>
</html>
