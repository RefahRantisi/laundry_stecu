<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'owner') {
        header("Location: dashboard_owner.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login UrbanClean</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,.1);
            text-align: center;
            width: 350px;
        }
        h2 {
            margin-bottom: 10px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
        }
        a {
            display: block;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            color: white;
        }
        .admin {
            background: #2c3e50;
        }
        .owner {
            background: #1abc9c;
        }
        a:hover {
            opacity: .9;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Masuk sebagai</h2>
    <p>Pilih jenis akun untuk melanjutkan</p>

    <a href="login.php" class="admin">Admin Cabang</a>
    <a href="login_owner.php" class="owner">Owner</a>
</div>

</body>
</html>
