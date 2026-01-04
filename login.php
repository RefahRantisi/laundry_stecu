<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
session_start();

include 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {

        // 🔒 Ambil hanya ADMIN
        $stmt = $conn->prepare("
            SELECT id, username, password, role, owner_id
            FROM users
            WHERE username = ? AND role = 'admin'
            LIMIT 1
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                // ✅ SET SESSION LENGKAP
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];       // 🔥 WAJIB
                $_SESSION['owner_id'] = $user['owner_id'];

                header("Location: dashboard.php");
                exit;

            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Akun admin tidak ditemukan!";
        }

        $stmt->close();
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login Admin - Laundry STECU</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f4;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            /* Tambahkan padding agar modal tidak nempel layar di HP */
        }

        /* Modal Overlay */
        .modal-overlay {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Modal Container */
        .modal-container {
            background: white;
            border-radius: 12px;
            width: 100%;
            /* Gunakan 100% agar fleksibel */
            max-width: 450px;
            /* Batas maksimal di PC */
            padding: 30px;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments for Mobile */
        @media (max-width: 480px) {
            .modal-container {
                padding: 20px;
                /* Perkecil padding di HP */
            }

            .modal-header h2 {
                font-size: 20px;
            }

            .submit-btn {
                padding: 12px;
            }
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .modal-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .modal-header h2 {
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .modal-header p {
            color: #777;
            font-size: 14px;
        }

        /* Alert */
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            word-wrap: break-word;
            /* Agar teks error tidak keluar box */
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        /* Form */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group label svg {
            margin-right: 8px;
            width: 18px;
            height: 18px;
        }

        .input-wrapper {
            position: relative;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            /* Ukuran 16px mencegah auto-zoom di iOS Safari */
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #2c3e50;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }

        .forgot-password a {
            color: #2c3e50;
            text-decoration: none;
            font-size: 13px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #34495e;
        }

        .modal-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
        }

        .modal-footer a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="modal-overlay">
        <div class="modal-container">
            <button class="close-btn" onclick="window.location.href='index.php'">&times;</button>

            <div class="modal-header">
                <h2>Masuk Sebagai Admin</h2>
                <p>Masukkan username dan password untuk melanjutkan</p>
            </div>

            <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                <div class="alert alert-success">
                    Anda berhasil logout.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
                <div class="alert alert-success">
                    Password berhasil direset! Silakan login dengan password baru Anda.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                        </svg>
                        Username
                    </label>
                    <input type="text" name="username" placeholder="Username" required>
                </div>

                <div class="form-group">
                    <label>
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Password
                    </label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="Masukkan password Anda"
                            required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            👁️
                        </button>
                    </div>
                </div>

                <div class="forgot-password">
                    <a href="forgot_password.php">Lupa Sandi?</a>
                </div>

                <button type="submit" class="submit-btn">Masuk</button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        }
    </script>
</body>

</html>