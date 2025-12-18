<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Koneksi database
$conn = new mysqli('localhost', 'root', '', 'laundry_stecu');

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $no_hp = trim($_POST['no_hp']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if (empty($nama) || empty($username) || empty($no_hp) || empty($password)) {
        $error = "Semua field harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password != $confirm) {
        $error = "Password tidak cocok!";
    } else {
        // Cek username
        $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Cek no HP
            $check_hp = $conn->query("SELECT id FROM users WHERE no_hp = '$no_hp'");
            if ($check_hp->num_rows > 0) {
                $error = "Nomor HP sudah terdaftar!";
            } else {
                // Insert user
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (nama, username, no_hp, password) VALUES ('$nama', '$username', '$no_hp', '$hashed')";
                
                if ($conn->query($sql)) {
                    $success = "Registrasi berhasil! Silakan login.";
                } else {
                    $error = "Gagal mendaftar: " . $conn->error;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Akun Baru - Laundry STECU</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Modal Container */
        .modal-container {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 480px;
            padding: 35px;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Close Button */
        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            transition: 0.3s;
        }

        .close-btn:hover {
            color: #333;
        }

        /* Header */
        .modal-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .modal-header h2 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .modal-header p {
            color: #777;
            font-size: 14px;
        }

        /* Alert */
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
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
        form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 20px;
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
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }

        input[type="text"]:focus,
        input[type="tel"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #2c3e50;
        }

        input::placeholder {
            color: #aaa;
        }

        /* Toggle Password Visibility */
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
        }

        /* Submit Button */
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
            transition: 0.3s;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: #34495e;
        }

        /* Footer Links */
        .modal-footer {
            text-align: center;
            margin-top: 20px;
        }

        .modal-footer a {
            color: #2c3e50;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .modal-footer a:hover {
            text-decoration: underline;
        }

        .modal-footer .divider {
            margin: 15px 0;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <div class="modal-container">
            <button class="close-btn" onclick="window.location.href='login.php'">&times;</button>
            
            <div class="modal-header">
                <h2>Daftar Akun Baru</h2>
                <p>Buat akun Laundry STECU untuk kemudahan layanan laundry</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                        </svg>
                        Nama Lengkap
                    </label>
                    <input 
                        type="text" 
                        name="nama" 
                        placeholder="Masukkan nama lengkap" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label>
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                        </svg>
                        Username
                    </label>
                    <input 
                        type="text" 
                        name="username" 
                        placeholder="Contoh: admin123" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label>
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                        Nomor Telepon
                    </label>
                    <input 
                        type="tel" 
                        name="no_hp" 
                        placeholder="Contoh: 08123456789" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label>
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        Password
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            placeholder="Masukkan password Anda" 
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            👁️
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        Konfirmasi Password
                    </label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            name="confirm_password" 
                            id="confirm_password"
                            placeholder="Ulangi password baru" 
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Daftar</button>
            </form>

            <div class="modal-footer">
                <p class="divider">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
            </div>
        </div>
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