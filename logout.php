<?php
session_start();

/*
|--------------------------------------------------------------------------
| VALIDASI LOGIN
|--------------------------------------------------------------------------
| Kalau belum login, tidak boleh logout
*/
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| HAPUS SEMUA DATA SESSION
|--------------------------------------------------------------------------
*/
$_SESSION = [];

// Hapus session di server
session_destroy();

/*
|--------------------------------------------------------------------------
| HAPUS COOKIE SESSION (PENTING)
|--------------------------------------------------------------------------
*/
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/*
|--------------------------------------------------------------------------
| REDIRECT KE LOGIN
|--------------------------------------------------------------------------
*/
header("Location: login.php?logout=success");
exit;
