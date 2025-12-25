<?php
session_start();

if (!isset($_SESSION['login'], $_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
