<?php
    if (isset($_SESSION['user_role'])){
        if ($_SESSION['user_role'] == 'admin') {
            session_destroy();
            header('Location: /development/public/index.php');
            exit();
        }
    }
?>