<?php   
    session_start();
    require_once __DIR__ .'/../config/config.php';
    
    $query = "
    SELECT hotels.id, hotels.title, hotels_coordinates.x, hotels_coordinates.y
    FROM hotels
    JOIN hotels_coordinates ON hotels.id = hotels_coordinates.id";

    $result = $conn->query($query);

    $hotels = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hotels[] = $row;
        }

        $jsonData = json_encode($hotels, JSON_PRETTY_PRINT);

        if ($jsonData === false) {
            die('JSON encoding failed: ' . json_last_error_msg());
        }

        file_put_contents('hotels_data.json', $jsonData);
    }
    include __DIR__. '/../includes/header.php' ;
?>

<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Bayatni.tn</title>

    <link rel="stylesheet" href="<?= $isLoggedIn ? __DIR__.'css/home_user.css' : 'css/index.css' ?>">

    <!-- TAILWIND CDN -->
    <script src="https://cdn.tailwindcss.com"></script> 
    <!-- Google Fonts CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Leaflet Maps CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body style="margin-top:0 !important; overflow : visible; ">

    <div id="background-container">
        <div class="bg-layer" id="bg1"></div>
        <div class="bg-layer" id="bg2"></div>
    </div>


    <?php if (isset($_GET['signup']) && $_GET['signup'] === 'success'): ?>
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" style="max-width:500px; display:flex; justify-self:center;" role="alert">
        <?php echo $_SESSION['success_message'] ; ?>
        </div>
    <?php endif; ?>

    <?php if (!$isLoggedIn): ?>
        <h1 class="title">Bayatni</h1>
        <p class="bio">Bayatni.tn est une plateforme tunisienne dédiée à la réservation d'hôtels.</br> Nous facilitons vos démarches en ligne pour vous offrir </br>une expérience simple, rapide et adaptée à vos besoins,</br> où que vous soyez en Tunisie ou en dehors !</br></p>
        
        <a href="/development/app/controllers/bookings/booking.php"><button class="main-btn">Réserver Maintenant</button></a>
    <?php elseif ($isLoggedIn):
            $userName = $_SESSION['user_name'];
            echo "<h1 class='mt-10 mb-10' style='color: white; font-size: 5rem; font-weight: 700; justify-self:center;'>Bienvenue, $userName</h1>";
            
            echo '<div class="search-container" style="width:60%; justify-self:center;">
                <form action="/development/app/views/search-results.php" method="GET" class="flex">
                    <input type="text" name="q" style="text-align:center; font-size:1.5rem;" placeholder="Recherche Rapide Intelligent" 
                           class="flex-grow p-3 rounded bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none text-white placeholder-white/70">
                    </button>
                </form>
            </div>';
        ?>
    <?php endif; ?>
    
    <div id=map> 
        <div class="loader">
            <span></span>
        </div>
    </div>

    <script src="/development/public/js/bg.js"></script>
    <script src="/development/public/js/map.js"></script>
</body>
</html>
