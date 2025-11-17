<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__.'/../config/checkrole.php';

$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['user_name']);
$current_page = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Bayatni.tn' : 'Bayatni.tn'; ?></title>
    
    <link rel="stylesheet" href="/development/public/css/header.css">
    <link rel="stylesheet" href="/development/public/css/<?= $isLoggedIn ? 'home_user.css' : 'index.css' ?>">
    <link rel="stylesheet" href="/development/public/css/booking.css">

    
    <!-- TAILWIND CDN -->
    <script src="https://cdn.tailwindcss.com"></script> 
    
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous"> 
    
    <!-- Google Fonts CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
</head>
<body>
    <div id="background-container">
        <div class="bg-layer" id="bg1"></div>
        <div class="bg-layer" id="bg2"></div>
    </div>

    <header class="flex justify-between items-center relative z-50">
        <a href="/development/public/index.php" class="domain">Bayatni.tn</a>
        
        <?php if ($isLoggedIn): ?>
        <nav class="nav">
            <div class="dropdown">
                <button class="btn dropdown-toggle d-flex align-items-center gap-1 text-white fw-semibold border-0 bg-transparent shadow-none" 
                        type="button" id="menuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= htmlspecialchars(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Mon Compte') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="position: absolute; z-index: 9999;">
                    <li><a class="dropdown-item" href="/development/app/views/profile.php">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/development/app/controllers/bookings/booking.php">Recherche Profond</a></li>
                    <li><a class="dropdown-item" href="/development/app/auth/logout.php">Déconnexion</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><div id="google_translate_element"></div></li>

                </ul>
            </div>
        </nav>
        <?php else: ?>
            <nav class="space-x-2">
                <?php if ($current_page !== 'signup.php'): ?>
                    <a href="/development/app/auth/signup.php"><button type="button" class="nav-btn-inverse">S'inscrire</button></a>
                <?php endif; ?>
                
                <?php if ($current_page !== 'signin.php'): ?>
                    <a href="/development/app/auth/signin.php"><button type="button" class="nav-btn">S'identifier</button></a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </header>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    </script>
