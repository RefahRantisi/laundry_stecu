<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Koneksi database
include 'koneksi.php';

$error = '';
$success = '';
$step = 1; // Step 1: Input username, Step 2: Set new password

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['check_username'])) {
        // Step 1: Cek username
        $username = trim($_POST['username']);
        
        if (empty($username)) {
            $error = "Username harus diisi!";
        } else {
            // Cek apakah username ada
            $sql = "SELECT * FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['reset_username'] = $username;
                $step = 2;
            } else {
                $error = "Username tidak ditemukan!";
            }
        }
    } elseif (isset($_POST['reset_password'])) {
        // Step 2: Reset password
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($new_password) || empty($confirm_password)) {
            $error = "Semua field harus diisi!";
            $step = 2;
        } elseif (strlen($new_password) < 6) {
            $error = "Password minimal 6 karakter!";
            $step = 2;
        } elseif ($new_password !== $confirm_password) {
            $error = "Password dan konfirmasi tidak sama!";
            $step = 2;
        } else {
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $username = $_SESSION['reset_username'];
            
            // Update password
            $sql = "UPDATE users SET password = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $hashed_password, $username);
            
            if ($stmt->execute()) {
                unset($_SESSION['reset_username']);
                header("Location: login.php?reset=success");
                exit;
            } else {
                $error = "Gagal mereset password!";
                $step = 2;
            }
        }
    }
}

// Jika ada session reset_username, langsung ke step 2
if (isset($_SESSION['reset_username']) && empty($_POST)) {
    $step = 2;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lupa Sandi - Laundry STECU</title>
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

        .modal-container {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 480px;
            padding: 35px;
            position: relative;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

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

        .modal-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .modal-header svg {
            width: 60px;
            height: 60px;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .modal-header h2 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .modal-header p {
            color: #777;
            font-size: 14px;
            line-height: 1.5;
        }

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
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #2c3e50;
        }

        input::placeholder {
            color: #aaa;
        }

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

        .back-btn {
            width: 100%;
            padding: 14px;
            background: #95a5a6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .back-btn:hover {
            background: #7f8c8d;
        }

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

        .password-requirements {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
            padding-left: 5px;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transition: 0.3s;
        }

        .step.active {
            background: #2c3e50;
            color: white;
        }

        .step.completed {
            background: #27ae60;
            color: white;
        }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <div class="modal-container">
            <button class="close-btn" onclick="window.location.href='login.php'">&times;</button>
            
            <div class="modal-header">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
                </svg>
                <h2>Lupa Sandi</h2>
                <p>
                    <?php if ($step == 1): ?>
                        Masukkan username Anda untuk mereset password
                    <?php else: ?>
                        Buat password baru untuk akun Anda
                    <?php endif; ?>
                </p>
            </div>

            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">1</div>
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">2</div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <!-- Step 1: Input Username -->
                <form method="POST">
                    <div class="form-group">
                        <label>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                            </svg>
                            Username
                        </label>
                        <input 
                            type="text" 
                            name="username" 
                            placeholder="Masukkan username Anda" 
                            required
                            autofocus
                        >
                    </div>

                    <button type="submit" name="check_username" class="submit-btn">Lanjutkan</button>
                    <button type="button" class="back-btn" onclick="window.location.href='login.php'">Kembali ke Login</button>
                </form>

            <?php else: ?>
                <!-- Step 2: Set New Password -->
                <form method="POST">
                    <div class="form-group">
                        <label>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Password Baru
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                name="new_password" 
                                id="new_password"
                                placeholder="Minimal 6 karakter" 
                                required
                                autofocus
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                👁️
                            </button>
                        </div>
                        <div class="password-requirements">
                            * Password minimal 6 karakter
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
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

                    <button type="submit" name="reset_password" class="submit-btn">Reset Password</button>
                    <button type="button" class="back-btn" onclick="window.location.href='login.php'">Batal</button>
                </form>
            <?php endif; ?>

            <div class="modal-footer">
                <a href="login.php">← Kembali ke Login</a>
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