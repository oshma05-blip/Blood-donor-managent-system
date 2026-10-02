<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login()
{
    if (empty($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

function require_admin()
{
    if (
        empty($_SESSION['user_id']) ||
        ($_SESSION['role'] ?? '') !== 'admin'
    ) {
        header("Location: ../login.php");
        exit;
    }
}

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>