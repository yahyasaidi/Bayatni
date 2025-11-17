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
?>
