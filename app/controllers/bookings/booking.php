<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__.'/../../../config/config.php';
$pageTitle = "Réservation d'hôtels";
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;


$region_filter = isset($_GET['region']) ? $_GET['region'] : '';
$price_filter = isset($_GET['price']) ? $_GET['price'] : 500;
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'recommended';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d', strtotime('+1 day'));
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+3 days'));
$guests = isset($_GET['guests']) ? $_GET['guests'] : 2;
$features_filter = isset($_GET['features']) ? $_GET['features'] : [];

$maxQuery = "SELECT * FROM hotels ORDER BY price DESC LIMIT 1";
$maxResult = $conn->query($maxQuery);

$minQuery = "SELECT * FROM hotels ORDER BY price ASC LIMIT 1";
$minResult = $conn->query($minQuery);

if ($maxResult && $minResult) {
    $high = $maxResult->fetch_assoc();
    $low = $minResult->fetch_assoc();
}


$sql = "SELECT h.*, COUNT(r.id) as review_count, AVG(r.rating) as avg_rating 
        FROM hotels h 
        LEFT JOIN reviews r ON h.id = r.hotel_id 
        WHERE 1=1";

if (!empty($region_filter)) {
    $sql .= " AND h.region = '$region_filter'";
}

if (!empty($price_filter)) {
    $sql .= " AND h.price <= $price_filter";
}

if (!empty($search_term)) {
    $sql .= " AND (h.title LIKE '%$search_term%' OR h.location LIKE '%$search_term%')";
}

if (!empty($features_filter)) {
    foreach ($features_filter as $feature) {
        $sql .= " AND h.features LIKE '%$feature%'";
    }
}

$sql .= " GROUP BY h.id";

switch ($sort_by) {
    case 'price-low':
        $sql .= " ORDER BY h.price ASC";
        break;
    case 'price-high':
        $sql .= " ORDER BY h.price DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY h.rating DESC";
        break;
    default:
        $sql .= " ORDER BY h.rating DESC, h.reviews_count DESC";
        break;
}

$result = $conn->query($sql);
$hotels_count = $result ? $result->num_rows : 0;

$features_sql = "SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(features, ',', numbers.n), ',', -1) feature
                FROM hotels
                JOIN (
                    SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
                ) numbers ON CHAR_LENGTH(features) - CHAR_LENGTH(REPLACE(features, ',', '')) >= numbers.n - 1
                WHERE features IS NOT NULL AND features != ''
                ORDER BY feature";

$features_result = $conn->query($features_sql);
$available_features = [];
if ($features_result) {
    while ($row = $features_result->fetch_assoc()) {
        $available_features[] = trim($row['feature']);
    }
}

function displayStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fas fa-star text-yellow-400"></i>';
        } else {
            $stars .= '<i class="far fa-star text-yellow-400"></i>';
        }
    }
    return $stars;
}

function formatPrice($price) {
    return number_format($price, 0) . ' DT';
}

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

include __DIR__.'/../../../includes/header.php';
?>

<main>

    <?php
        if (isset($booking_message)) {
            echo $booking_message;
        } 
    ?>

        <div class="glass-card rounded-2xl mb-10 text-white">
            
            <form method="GET" action="booking.php" class="mt-20">
                    <div class="flex flex-col md:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <label for="search" class="block mb-2 font-medium">Recherche</label>
                            <div class="relative">
                                <input type="text" name="search" id="search" 
                                   class="w-full p-3 pl-10 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white placeholder-white/70"
                                   placeholder="Nom d'hôtel ou destination" value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <label for="region" class="block mb-2 font-medium">Région</label>
                        <select id="region" name="region" 
                                class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                            <option value="">Toutes les régions</option>
                            <option value="tunis" <?php if($region_filter == 'tunis') echo 'selected'; ?>>Grand Tunis</option>
                            <option value="hammamet" <?php if($region_filter == 'hammamet') echo 'selected'; ?>>Hammamet & Nabeul</option>
                            <option value="sousse" <?php if($region_filter == 'sousse') echo 'selected'; ?>>Sousse & Monastir</option>
                            <option value="djerba" <?php if($region_filter == 'djerba') echo 'selected'; ?>>Djerba & Sud</option>
                            <option value="tabarka" <?php if($region_filter == 'tabarka') echo 'selected'; ?>>Tabarka & Nord</option>
                        </select>
                    </div>
                    
                    <div class="w-full md:w-1/4">
                        <label for="sort" class="block mb-2 font-medium">Trier par</label>
                        <select id="sort" name="sort" 
                                class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                            <option value="recommended" <?php if($sort_by == 'recommended') echo 'selected'; ?>>Recommandés</option>
                            <option value="price-low" <?php if($sort_by == 'price-low') echo 'selected'; ?>>Prix (bas-haut)</option>
                            <option value="price-high" <?php if($sort_by == 'price-high') echo 'selected'; ?>>Prix (haut-bas)</option>
                            <option value="rating" <?php if($sort_by == 'rating') echo 'selected'; ?>>Meilleures notes</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="check_in" class="block mb-2 font-medium">Date d'arrivée</label>
                        <input type="date" id="check_in" name="check_in" 
                               class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white"
                               value="<?php echo htmlspecialchars($check_in); ?>" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div>
                        <label for="check_out" class="block mb-2 font-medium">Date de départ</label>
                        <input type="date" id="check_out" name="check_out" 
                               class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white"
                               value="<?php echo htmlspecialchars($check_out); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                    
                    <div>
                        <label for="guests" class="block mb-2 font-medium">Voyageurs</label>
                        <select id="guests" name="guests" 
                                class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                            <option value="1" <?php if($guests == 1) echo 'selected'; ?>>1 personne</option>
                            <option value="2" <?php if($guests == 2) echo 'selected'; ?>>2 personnes</option>
                            <option value="3" <?php if($guests == 3) echo 'selected'; ?>>3 personnes</option>
                            <option value="4" <?php if($guests == 4) echo 'selected'; ?>>4 personnes</option>
                            <option value="5" <?php if($guests == 5) echo 'selected'; ?>>5 personnes</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="price" class="block mb-2 font-medium">Budget maximum par nuit: <span id="price-value"><?php echo $price_filter; ?> DT</span></label>
                    <input type="range" id="price" name="price" 
                           class="w-full h-2 rounded-lg appearance-none cursor-pointer"
                           min="50" max="1000" value="<?php echo $price_filter; ?>" step="10">
                    <div class="flex justify-between text-sm mt-1">
                        <?php
                            echo "<span>" . formatPrice($low['price']) . "</span>";
                            echo "<span>" . formatPrice($high['price']) . "</span>";
                        ?>
                    </div>
                </div>
                
                <?php if (!empty($available_features)): ?>
                <div class="mb-6">
                    <label class="block mb-2 font-medium">Équipements</label>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach($available_features as $feature): ?>
                        <label class="inline-flex items-center px-3 py-2 rounded-full bg-white/20 backdrop-blur-sm cursor-pointer hover:bg-white/30 transition">
                            <input type="checkbox" name="features[]" value="<?php echo $feature; ?>" 
                                   class="mr-2 accent-primary-500" 
                                   <?php if(in_array($feature, $features_filter)) echo 'checked'; ?>>
                            <?php echo ucfirst($feature); ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <center><button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition shadow-lg hover:shadow-xl"><i class="fas fa-search mr-2"></i>Rechercher</button></center>
            </form>  
        <div class="space-y-10">
            <?php
            if ($hotels_count > 0) {
                $delay = 1;
                while($row = $result->fetch_assoc()) {
                    $delay_class = "delay-" . (($delay % 5) + 1) * 100;
                    ?>
                    <div class="hotel-card glass-card rounded-xl overflow-hidden animate-fade-in <?php echo $delay_class; ?>" data-hotel-id="<?php echo $row['id']; ?>">
                        <div class="flex flex-col md:flex-row">
                            <div class="hotel-image md:w-1/3 h-64 md:h-auto overflow-hidden">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($row['title']); ?>"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-start">
                                        <h3 class="text-xl font-bold text-white mb-2"><?php echo htmlspecialchars($row['title']); ?></h3>
                                        <div class="text-2xl font-bold text-white"><?php echo formatPrice($row['price']); ?> <span class="text-sm font-normal opacity-80">/ nuit</span></div>
                                    </div>
                                    <div class="flex items-center mb-3">
                                        <div class="flex mr-2">
                                            <?php echo displayStars($row['rating']); ?>
                                        </div>
                                        <span class="text-white/80">(<?php echo $row['reviews_count']; ?> avis)</span>
                                    </div>
                                    <p class="text-white/90 mb-3">
                                        <i class="fas fa-map-marker-alt mr-2 text-primary-300"></i>
                                        <?php echo htmlspecialchars($row['location']); ?>
                                    </p>
                                    <?php echo displayFeatures($row['features']); ?>
                                </div>
                                <?php if ($is_logged_in): ?>
                                    <div class="mt-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                                        <a href="/development/app/views/hotel-details.php?id=<?php echo $row['id']; ?>" class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white rounded-lg hover:bg-white/30 transition text-center">
                                            <i class="fas fa-info-circle mr-2"></i>Détails
                                        </a>
                                        <button onclick="showBookingForm(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['title'])); ?>', <?php echo $row['price']; ?>)" 
                                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition shadow-lg hover:shadow-xl text-center">
                                            <i class="fas fa-calendar-check mr-2"></i>Réserver maintenant
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <p>Veuillez vous connectez ou s'inscrire !</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $delay++;
                }
            } else {
                echo '<div class="glass-card rounded-xl p-8 text-center text-white">
                        <i class="fas fa-search fa-3x mb-4 opacity-70"></i>
                        <h3 class="text-xl font-semibold mb-2">Aucun hôtel ne correspond à vos critères</h3>
                        <p>Veuillez modifier vos filtres pour trouver des résultats.</p>
                      </div>';
            }
            ?>
        </div>
    </div>
</main>

<div id="bookingModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900 opacity-90"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom glass-card rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-2xl leading-6 font-bold text-white mb-4" id="modal-title">
                            Réserver votre séjour
                        </h3>
                        <form method="POST" action="process_booking.php" id="booking-form">
                            <input type="hidden" name="hotel_id" id="modal-hotel-id">
                            <input type="hidden" name="book_hotel" value="1">
                            <input type="hidden" name="total_price" id="total-price">
                            <input type="hidden" name="payment_status" id="payment-status" value="pending">
                            
                            <div class="mb-4">
                                <h4 id="modal-hotel-title" class="text-lg font-semibold text-white"></h4>
                                <p class="text-white/80">Prix de base par nuit: <span id="modal-hotel-price"></span> DT</p>
                            </div>
                            
                            <div id="booking-details-form">
                                <div class="mb-4">
                                    <label for="modal-room-type" class="block text-sm font-medium text-white mb-1">Type de chambre</label>
                                    <select class="w-full p-2 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white" 
                                           id="modal-room-type" name="room_type" required>
                                        <option value="standard">Standard</option>
                                        <option value="deluxe">Deluxe (+50%)</option>
                                        <option value="suite">Suite (+100%)</option>
                                        <option value="family">Familiale (+80%)</option>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="modal-check-in" class="block text-sm font-medium text-white mb-1">Date d'arrivée</label>
                                    <input type="date" class="w-full p-2 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white" 
                                           id="modal-check-in" name="check_in" required 
                                           min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="modal-check-out" class="block text-sm font-medium text-white mb-1">Date de départ</label>
                                    <input type="date" class="w-full p-2 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white" 
                                           id="modal-check-out" name="check_out" required 
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="modal-guests" class="block text-sm font-medium text-white mb-1">Nombre de voyageurs</label>
                                    <select class="w-full p-2 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white" 
                                           id="modal-guests" name="guests" required>
                                        <option value="1" selected>1 personne</option>
                                        <option value="2">2 personnes</option>
                                        <option value="3">3 personnes</option>
                                        <option value="4">4 personnes</option>
                                        <option value="5">5 personnes</option>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex items-center">
                                        <input class="mr-2 accent-primary-500" type="checkbox" id="terms-check" required>
                                        <label class="text-sm text-white" for="terms-check">
                                            J'accepte les conditions générales de vente
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="payment-form" class="hidden">
                                <div class="mb-4">
                                    <label for="payment-method" class="block text-sm font-medium text-white mb-1">Méthode de paiement</label>
                                    <select class="w-full p-2 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white" 
                                           id="payment-method" name="payment_method" required>
                                        <option value="credit-card">Carte de crédit</option>
                                    </select>
                                </div>
                                
                            </div>
                            
                            <div class="mb-4 p-4 bg-white/10 backdrop-blur-sm rounded-lg">
                                <h5 class="font-semibold text-white mb-2">Résumé de la réservation</h5>
                                <div id="booking-dates" class="text-white/80"></div>
                                <div id="booking-nights" class="text-white/80"></div>
                                <div id="booking-room-type" class="text-white/80"></div>
                                <div id="booking-price-per-night" class="text-white/80"></div>
                                <div id="booking-subtotal" class="text-white/80"></div>
                                <div id="booking-tax" class="text-white/80"></div>
                                <div id="booking-total" class="font-bold text-white mt-2"></div>
                            </div>
                            
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeBookingModal()" 
                                        class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white rounded-lg hover:bg-white/30 transition">
                                    Annuler
                                </button>
                                <button type="button" id="confirm-btn" onclick="proceedToPayment()" 
                                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition shadow-lg hover:shadow-xl">
                                    Réserver maintenant
                                </button>
                                <button type="button" id="pay-now-btn" onclick="processPayment()" 
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition shadow-lg hover:shadow-xl hidden">
                                    Payer maintenant
                                </button>
                                <button type="button" id="pay-later-btn" onclick="payLater()" 
                                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition shadow-lg hover:shadow-xl hidden">
                                    Payer plus tard
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

include __DIR__.'/../../../includes/footer.php';


$conn->close();
?>

<script src="/development/public/js/book.js"></script>
<script src="/development/public/js/bg.js"></script>
<script>
function processPayment() {
    document.getElementById('payment-status').value = 'confirmed';
    document.getElementById('booking-form').submit();
}

function payLater() {
    document.getElementById('payment-status').value = 'pending';
    document.getElementById('booking-form').submit();
}
</script>
