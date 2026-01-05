<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['cabang_id'])) {
    die('Cabang tidak ditemukan. Silakan login ulang.');
}

$cabang_id = (int) $_SESSION['cabang_id'];

