<?php
session_start();

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
header("Location: index.php?");
exit;
