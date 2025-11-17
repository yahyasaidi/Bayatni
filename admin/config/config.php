<?php
$host = 'localhost';
$db   = 'bayatni_db';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /development/admin/app/auth/login.php");
        exit();
    }
}
?>
