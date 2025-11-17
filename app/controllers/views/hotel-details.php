<?php
session_start();
require_once __DIR__.'/../../../config/config.php';

$isLoggedIn = isset($_SESSION['user_name']);

if (!$isLoggedIn) {
    header("Location: /development/app/views/index.php");
    exit();
}

// Check if hotel ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /development/app/views/index.php");
    exit();
}

$hotelId = intval($_GET['id']);

// Fetch hotel details
$query = "
    SELECT h.*, hc.x, hc.y 
    FROM hotels h
    LEFT JOIN hotels_coordinates hc ON h.id = hc.id
    WHERE h.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $hotelId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /development/app/views/index.php");
    exit();
}

$hotel = $result->fetch_assoc();

// Fetch reviews for this hotel
$reviewsQuery = "
    SELECT r.*, u.fullname 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.hotel_id = ?
    ORDER BY r.review_date DESC";

$reviewsStmt = $conn->prepare($reviewsQuery);
$reviewsStmt->bind_param("i", $hotelId);
$reviewsStmt->execute();
$reviewsResult = $reviewsStmt->get_result();

$reviews = [];
while ($review = $reviewsResult->fetch_assoc()) {
    $reviews[] = $review;
}

include __DIR__."/../../../includes/header.php";
?>

<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo htmlspecialchars($hotel['title']); ?> - Bayatni.tn</title>

    <link rel="stylesheet" href="/development/public/css/home_user.css">
    <link rel="stylesheet" href="/development/public/css/booking.css">

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
</head>

<body style="margin-top:0 !important; overflow: auto;">
    <div id="background-container">
        <div class="bg-layer" id="bg1"></div>
        <div class="bg-layer" id="bg2"></div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <a href="javascript:history.back()" class="text-white hover:text-blue-300 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour aux résultats
            </a>
        </div>

        <div class="bg-white/10 backdrop-blur-md rounded-lg overflow-hidden shadow-lg mb-8">
            <div class="md:flex">
                <div class="md:w-1/2">
                    <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($hotel['title']); ?>" 
                         class="w-full h-64 md:h-full object-cover">
                </div>
                <div class="md:w-1/2 p-6">
                    <h1 class="text-3xl font-bold text-white mb-2"><?php echo htmlspecialchars($hotel['title']); ?></h1>
                    <p class="text-white/80 mb-4"><?php echo htmlspecialchars($hotel['location']); ?></p>
                    
                    <div class="flex items-center mb-4">
                        <?php for ($i = 0; $i < $hotel['rating']; $i++): ?>
                            <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                            </svg>
                        <?php endfor; ?>
                        <?php for ($i = $hotel['rating']; $i < 5; $i++): ?>
                            <svg class="w-5 h-5 text-gray-400 fill-current" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                            </svg>
                        <?php endfor; ?>
                        <span class="ml-2 text-white/80">(<?php echo $hotel['reviews_count']; ?> avis)</span>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-2xl font-bold text-white"><?php echo $hotel['price']; ?> TND <span class="text-sm font-normal">/ nuit</span></p>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-2">Caractéristiques</h3>
                        <div class="flex flex-wrap">
                            <?php 
                            if ($hotel['features']) {
                                $features = explode(',', $hotel['features']);
                                $featureIcons = [
                                    'piscine' => '🏊',
                                    'plage' => '🏖️',
                                    'restaurant' => '🍽️',
                                    'spa' => '💆',
                                    'wifi' => '📶',
                                    'vue mer' => '🌊'
                                ];
                                
                                foreach ($features as $feature) {
                                    $icon = isset($featureIcons[$feature]) ? $featureIcons[$feature] : '';
                                    echo '<span class="inline-block bg-blue-100 text-blue-800 rounded-full px-3 py-1 text-sm font-semibold mr-2 mb-2">' . 
                                         $icon . ' ' . htmlspecialchars($feature) . '</span>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button id="reserveBtn" class="bg-green-500 hover:bg-green-600 text-white py-2 px-6 rounded-lg transition-colors">
                            Réserver maintenant
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-3 gap-10">
            <div class="col-span-1">
                <div class="bg-white/10 backdrop-blur-md rounded-lg shadow-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-white mb-4">Localisation</h2>
                    <div id="hotel-map" class="h-64 rounded-lg"></div>
                </div>
            </div>
            <div class="col-span-1">
                <div class="bg-white/10 backdrop-blur-md rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-white mb-4">Avis (<?php echo count($reviews); ?>)</h2>
                    <?php if (empty($reviews)): ?>
                        <p class="text-white/80">Aucun avis pour cet hôtel.</p>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($reviews as $review): ?>
                                <div class="border-b border-white/20 pb-4 last:border-0 last:pb-0">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold text-white"><?php echo htmlspecialchars($review['fullname']); ?></p>
                                            <div class="flex items-center mt-1">
                                                <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                                    <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 24 24">
                                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                                    </svg>
                                                <?php endfor; ?>
                                                <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                                                    <svg class="w-4 h-4 text-gray-400 fill-current" viewBox="0 0 24 24">
                                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                                    </svg>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <span class="text-sm text-white/60">
                                            <?php echo date('d/m/Y', strtotime($review['review_date'])); ?>
                                        </span>
                                    </div>
                                    <?php if ($review['comment']): ?>
                                        <p class="mt-2 text-white/80"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div id="reservation-form" class="bg-white/10 backdrop-blur-md rounded-lg shadow-lg p-6 h-fit hidden">
    <h2 class="text-2xl font-bold text-white mb-4">Réservation</h2>
    <form id="booking-form" action="/development/app/controllers/bookings/process_booking.php" method="POST">
        <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
        <input type="hidden" name="price_per_night" value="<?php echo $hotel['price']; ?>">
        <input type="hidden" name="total_price" id="hidden-total-price" value="0">
        <input type="hidden" name="payment_status" id="payment-status" value="pending">
        <input type="hidden" name="book_hotel" value="1">
        
        <div class="mb-4">
            <label for="check_in" class="block text-white mb-2">Date d'arrivée</label>
            <input type="date" id="check_in" name="check_in" required
                   class="w-full p-2 rounded bg-white/20 border border-white/30 text-white">
        </div>
        
        <div class="mb-4">
            <label for="check_out" class="block text-white mb-2">Date de départ</label>
            <input type="date" id="check_out" name="check_out" required
                   class="w-full p-2 rounded bg-white/20 border border-white/30 text-white">
        </div>
        
        <div class="mb-4">
            <label for="guests" class="block text-white mb-2">Nombre de personnes</label>
            <select id="guests" name="guests" required
                    class="w-full p-2 rounded bg-white/20 border border-white/30 text-white">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?> personne<?php echo $i > 1 ? 's' : ''; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="mb-4">
            <label for="room_type" class="block text-white mb-2">Type de chambre</label>
            <select id="room_type" name="room_type" required
                    class="w-full p-2 rounded bg-white/20 border border-white/30 text-white">
                <option value="standard">Chambre Standard</option>
                <option value="deluxe">Chambre Deluxe</option>
                <option value="suite">Suite</option>
            </select>
        </div>
        
        <div class="mb-6">
            <label for="payment_method" class="block text-white mb-2">Méthode de paiement</label>
            <select id="payment_method" name="payment_method" required
                    class="w-full p-2 rounded bg-white/20 border border-white/30 text-white">
                <option value="credit_card" selected>Carte de crédit</option>
            </select>
        </div>
        
        <div class="border-t border-white/20 pt-4 mb-4">
            <div class="flex justify-between text-white mb-2">
                <span>Prix par nuit:</span>
                <span><?php echo $hotel['price']; ?> TND</span>
            </div>
            <div class="flex justify-between text-white mb-2">
                <span>Nombre de nuits:</span>
                <span id="nights-count">0</span>
            </div>
            <div class="flex justify-between text-white font-bold text-lg">
                <span>Total:</span>
                <span id="total-price">0 TND</span>
            </div>
        </div>
        
        <div class="flex gap-2">
            <button type="button" onclick="processPayment('confirmed')" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors">
                Payer maintenant
            </button>
            <button type="button" onclick="processPayment('pending')" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg transition-colors">
                Payer plus tard
            </button>
        </div>
    </form>
</div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Background animation
        const bg1 = document.getElementById('bg1');
        const bg2 = document.getElementById('bg2');
        
        const images = [
            '/development/public/images/bg1.jpg',
            '/development/public/images/bg2.jpg',
            '/development/public/images/bg3.jpg'
        ];
        
        let currentIndex = 0;
        
        function changeBackground() {
            const nextIndex = (currentIndex + 1) % images.length;
            
            const currentBg = currentIndex % 2 === 0 ? bg1 : bg2;
            const nextBg = currentIndex % 2 === 0 ? bg2 : bg1;
            
            nextBg.style.backgroundImage = `url(${images[nextIndex]})`;
            nextBg.classList.add('visible');
            currentBg.classList.remove('visible');
            
            currentIndex = nextIndex;
        }
        
        // Set initial background
        bg1.style.backgroundImage = `url(${images[0]})`;
        bg1.classList.add('visible');
        
        // Change background every 10 seconds
        setInterval(changeBackground, 10000);
        
        // Initialize map
        const hotelMap = L.map('hotel-map').setView([
            <?php echo $hotel['x']; ?>, 
            <?php echo $hotel['y']; ?>
        ], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(hotelMap);
        
        L.marker([
            <?php echo $hotel['x']; ?>, 
            <?php echo $hotel['y']; ?>
        ]).addTo(hotelMap)
            .bindPopup("<?php echo htmlspecialchars($hotel['title']); ?>")
            .openPopup();
        
        // Reservation form toggle
        const reserveBtn = document.getElementById('reserveBtn');
        const reservationForm = document.getElementById('reservation-form');
        
        if (reserveBtn && reservationForm) {
            console.log("Reserve button and form found");
            reserveBtn.addEventListener('click', function() {
                console.log("Reserve button clicked");
                reservationForm.classList.toggle('hidden');
                if (!reservationForm.classList.contains('hidden')) {
                    reservationForm.scrollIntoView({ behavior: 'smooth' });
                }
            });
        } else {
            console.error("Reserve button or form not found");
        }
        
        // Save button functionality
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                // You can implement saving to favorites here
                alert('Hôtel sauvegardé dans vos favoris!');
            });
        }
        
        // Calculate total price
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        const nightsCount = document.getElementById('nights-count');
        const totalPrice = document.getElementById('total-price');
        const hiddenTotalPrice = document.getElementById('hidden-total-price');
        const pricePerNight = <?php echo $hotel['price']; ?>;
        
        function calculateTotal() {
            const checkIn = new Date(checkInInput.value);
            const checkOut = new Date(checkOutInput.value);
            
            if (checkIn && checkOut && checkOut > checkIn) {
                const diffTime = Math.abs(checkOut - checkIn);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                nightsCount.textContent = diffDays;
                const totalPriceValue = (diffDays * pricePerNight).toFixed(2);
                totalPrice.textContent = totalPriceValue + ' TND';
                hiddenTotalPrice.value = totalPriceValue;
            } else {
                nightsCount.textContent = '0';
                totalPrice.textContent = '0 TND';
                hiddenTotalPrice.value = 0;
            }
        }
        
        if (checkInInput && checkOutInput) {
            checkInInput.addEventListener('change', calculateTotal);
            checkOutInput.addEventListener('change', calculateTotal);
            
            // Set minimum dates
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const dayAfterTomorrow = new Date(today);
            dayAfterTomorrow.setDate(dayAfterTomorrow.getDate() + 2);
            
            const formatDate = date => {
                return date.toISOString().split('T')[0];
            };
            
            checkInInput.min = formatDate(today);
            checkInInput.value = formatDate(tomorrow);
            
            checkInInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const nextDay = new Date(selectedDate);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutInput.min = formatDate(nextDay);
                
                if (checkOutInput.value && new Date(checkOutInput.value) <= selectedDate) {
                    checkOutInput.value = formatDate(nextDay);
                }
                
                calculateTotal();
            });
            
            checkOutInput.min = formatDate(tomorrow);
            checkOutInput.value = formatDate(dayAfterTomorrow);
            
            // Calculate initial total
            calculateTotal();
        }
    });

    // Form submission handling
    function processPayment(status) {
        const form = document.getElementById('booking-form');
        const paymentStatus = document.getElementById('payment-status');
        const hiddenTotalPrice = document.getElementById('hidden-total-price');
        const totalPriceText = document.getElementById('total-price').textContent;
        
        // Extract the numeric value from the total price text
        const totalPrice = parseFloat(totalPriceText.replace(' TND', ''));
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Check if dates are selected
        const checkIn = new Date(document.getElementById('check_in').value);
        const checkOut = new Date(document.getElementById('check_out').value);
        
        if (!checkIn || !checkOut || checkOut <= checkIn) {
            alert('Veuillez sélectionner des dates valides');
            return;
        }
        
        // Set payment status and total price
        paymentStatus.value = status;
        hiddenTotalPrice.value = totalPrice;
        
        // Submit the form
        form.submit();
    }
</script>
</body>
</html>
