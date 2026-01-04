<?php
session_start();

if (
    !isset($_SESSION['login']) ||
    $_SESSION['role'] !== 'owner'
) {
    header("Location: login_owner.php");
    exit;
}
