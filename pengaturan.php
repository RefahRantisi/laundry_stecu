<?php
session_start();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Pengaturan Laundry</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
            margin: 0
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 30px
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px
        }

        a {
            display: block;
            padding: 12px;
            background: #2c3e50;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 0
        }

        a:hover {
            background: #1abc9c
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Pengaturan Laundry</h2>
        <div class="card">
            <a href="pengaturan_paket.php">⚙️ Pengaturan Paket</a>
            <a href="pengaturan_status.php">⚙️ Pengaturan Status</a>
            <a href="pengaturan_alur.php">⚙️ Pengaturan Alur Paket</a>
        </div>
    </div>
</body>

</html>