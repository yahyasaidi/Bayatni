<?php
session_start();
session_destroy();
header("Location: /development/public/index.php");
exit;