<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: select_login.php");
    exit;
}

