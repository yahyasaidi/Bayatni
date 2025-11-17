<?php
session_start();
require_once __DIR__ .'/../../../config/config.php';

$isLoggedIn = isset($_SESSION['user_name']);

if (!$isLoggedIn) {
    header("Location: /development/app/views/index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /development/app/views/index.php");
    exit();
}

$bookingId = intval($_GET['id']);
$userId = $_SESSION['user_id'];

$query = "
    SELECT b.*, h.title as hotel_name, h.location, h.image_url, h.features
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    WHERE b.id = ? AND b.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /development/app/views/index.php");
    exit();
}

$booking = $result->fetch_assoc();

function displayFeatures($features) {
    if (empty($features)) return '';
    
    $features_array = explode(',', $features);
    $html = '<div class="flex flex-wrap gap-2 mt-3">';
    
    foreach ($features_array as $feature) {
        $feature = trim($feature);
        $icon = '';
        
        switch ($feature) {
            case 'piscine':
                $icon = '<i class="fas fa-swimming-pool"></i>';
                break;
            case 'plage':
                $icon = '<i class="fas fa-umbrella-beach"></i>';
                break;
            case 'restaurant':
                $icon = '<i class="fas fa-utensils"></i>';
                break;
            case 'spa':
                $icon = '<i class="fas fa-spa"></i>';
                break;
            case 'vue mer':
                $icon = '<i class="fas fa-water"></i>';
                break;
            default:
                $icon = '<i class="fas fa-check"></i>';
        }
        
        $html .= '<span class="px-3 py-1 bg-white/30 backdrop-blur-sm rounded-full text-sm flex items-center gap-1">' . $icon . ' ' . ucfirst($feature) . '</span>';
    }
    
    $html .= '</div>';
    return $html;
}

include __DIR__."/../../../includes/header.php";
?>

<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Confirmation de réservation - Bayatni.tn</title>

    <link rel="stylesheet" href="/development/public/css/home_user.css">
    <link rel="stylesheet" href="/development/public/css/booking.css">

    <!-- TAILWIND CDN -->
    <script src="https://cdn.tailwindcss.com"></script> 
    <!-- Google Fonts CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body style="margin-top:0 !important; overflow: auto;">

    <div id="background-container">
        <div class="bg-layer" id="bg1"></div>
        <div class="bg-layer" id="bg2"></div>
    </div>
    
    <main>
        <div class="container mx-auto px-4 py-8">
            <?php if (isset($_SESSION['success_message']) && (isset($_SESSION['payment_status'])) && $_SESSION['payment_status']=="pending" ): ?>
                <div style="width:40%; justify-self:center; text-align:center;" class="p-4 mt-5 text-sm text-yellow-700 bg-yellow-100 rounded-lg">
                    <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']); 
                    ?>
                </div>
            <?php endif; ?>

            <div class="max-w-2xl mx-auto">
                <div class="glass-card rounded-xl p-8">
                    <div class="text-center mb-8">
                        <?php if ($booking['status'] === 'confirmed'): ?>
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-500 text-white mb-4">
                                <i class="fas fa-check text-2xl"></i>
                            </div>
                            <h1 class="text-3xl font-bold text-white">Réservation Confirmée!</h1>
                        <?php else: ?>
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-500 text-white mb-4">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                            <h1 class="text-3xl font-bold text-white">Réservation en Attente</h1>
                        <?php endif; ?>
                        <p class="text-white/80 mt-2">Votre réservation a été enregistrée avec succès.</p>
                    </div>
                    
                    <div class="mb-6">
                        <div class="flex items-center mb-4">
                            <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" 
                                alt="<?php echo htmlspecialchars($booking['hotel_name']); ?>" 
                                class="w-20 h-20 object-cover rounded-lg mr-4">
                            <div>
                                <h2 class="text-xl font-bold text-white"><?php echo htmlspecialchars($booking['hotel_name']); ?></h2>
                                <p class="text-white/80"><?php echo htmlspecialchars($booking['location']); ?></p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-white/60 text-sm">Numéro de réservation</p>
                                <p class="text-white font-semibold">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                            </div>
                            <div>
                                <p class="text-white/60 text-sm">Statut</p>
                                <?php if ($booking['status'] === 'confirmed'): ?>
                                    <p class="inline-block px-2 py-1 bg-green-500 text-white text-xs rounded-full">
                                        Confirmée
                                    </p>
                                <?php else: ?>
                                    <p class="inline-block px-2 py-1 bg-yellow-500 text-white text-xs rounded-full">
                                        En attente
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="text-white/60 text-sm">Date d'arrivée</p>
                                <p class="text-white"><?php echo date('d/m/Y', strtotime($booking['check_in'])); ?></p>
                            </div>
                            <div>
                                <p class="text-white/60 text-sm">Date de départ</p>
                                <p class="text-white"><?php echo date('d/m/Y', strtotime($booking['check_out'])); ?></p>
                            </div>
                            <div>
                                <p class="text-white/60 text-sm">Nombre de personnes</p>
                                <p class="text-white"><?php echo $booking['guests']; ?></p>
                            </div>
                            <div>
                                <p class="text-white/60 text-sm">Type de chambre</p>
                                <p class="text-white"><?php echo ucfirst($booking['room_type']); ?></p>
                            </div>
                        </div>
                        
                        <?php if (!empty($booking['features'])): ?>
                            <div class="mb-4">
                                <p class="text-white/60 text-sm mb-2">Équipements</p>
                                <?php echo displayFeatures($booking['features']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="border-t border-white/20 pt-4">
                            <div class="flex justify-between text-white font-bold text-lg">
                                <span>Total:</span>
                                <span><?php echo $booking['total_price']; ?> DT</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center space-x-4">
                        <a href="/development/public/index.php" class="inline-block px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition shadow-lg hover:shadow-xl">
                            <i class="fas fa-home mr-2"></i>Retour à l'accueil
                        </a>
                        <button onclick="window.print()" class="inline-block px-6 py-3 bg-white/20 hover:bg-white/30 text-white font-medium rounded-lg transition">
                            <i class="fas fa-print mr-2"></i>Imprimer
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="/development/public/js/bg.js"></script>
</body>
</html>
