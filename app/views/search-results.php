<?php
session_start();
require_once __DIR__.'/../../config/config.php';

$isLoggedIn = isset($_SESSION['user_name']);
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$isLoggedIn) {
    header("Location: /development/app/views/index.php");
    exit();
}

$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';

include __DIR__."/../../includes/header.php";

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
?>

<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Recherche - Bayatni.tn</title>

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

            <div class="glass-card rounded-2xl mb-10 text-white">
                <form id="searchForm" class="mt-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <label for="searchInput" class="block mb-2 font-medium">Recherche</label>
                            <div class="relative">
                                <input type="text" name="q" id="searchInput" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                                    class="w-full p-3 pl-10 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white placeholder-white/70"
                                    placeholder="Nom d'hôtel ou destination">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                </div>
                            </div>
                        </div>
                        <div class="md:w-auto flex items-end">
                            <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition shadow-lg hover:shadow-xl">
                                <i class="fas fa-search mr-2"></i>Rechercher
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="results" style="width:80%; justify-self:center;" class="space-y-10">

                <div class="glass-card rounded-xl p-8 text-center text-white">
                    <p class="mt-4">Recherche en cours...</p>
                </div>
                
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
                            <form method="POST" action="/development/app/controllers/bookings/process_booking.php" id="booking-form">
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

    <script>
        // JavaScript version of displayFeatures function for client-side rendering
        function displayFeatures(features) {
            if (!features) return '';
            
            const featuresArray = features.split(',');
            let html = '<div class="flex flex-wrap gap-2 mt-3">';
            
            featuresArray.forEach(feature => {
                feature = feature.trim();
                let icon = '';
                
                switch (feature) {
                    case 'piscine':
                        icon = '<i class="fas fa-swimming-pool"></i>';
                        break;
                    case 'plage':
                        icon = '<i class="fas fa-umbrella-beach"></i>';
                        break;
                    case 'restaurant':
                        icon = '<i class="fas fa-utensils"></i>';
                        break;
                    case 'spa':
                        icon = '<i class="fas fa-spa"></i>';
                        break;
                    case 'vue mer':
                        icon = '<i class="fas fa-water"></i>';
                        break;
                    default:
                        icon = '<i class="fas fa-check"></i>';
                }
                
                html += `<span class="px-3 py-1 bg-white/30 backdrop-blur-sm rounded-full text-sm flex items-center gap-1">${icon} ${feature.charAt(0).toUpperCase() + feature.slice(1)}</span>`;
            });
            
            html += '</div>';
            return html;
        }

        // JavaScript version of displayStars function for client-side rendering
        function displayStars(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    stars += '<i class="fas fa-star text-yellow-400"></i>';
                } else {
                    stars += '<i class="far fa-star text-yellow-400"></i>';
                }
            }
            return stars;
        }

        // JavaScript version of formatPrice function for client-side rendering
        function formatPrice(price) {
            return new Intl.NumberFormat('fr-TN', { maximumFractionDigits: 0 }).format(price) + ' DT';
        }

            // Handle search form submission
            const searchForm = document.getElementById('searchForm');
            const searchInput = document.getElementById('searchInput');
            const resultsContainer = document.getElementById('results');
            
            // Function to fetch search results from Flask API
            async function fetchSearchResults(query) {
                try {
                    resultsContainer.innerHTML = ``;
                    
                    const response = await fetch(`http://localhost:5000/search?q=${encodeURIComponent(query)}`);
                    const data = await response.json();
                    
                    if (data.length === 0) {
                        resultsContainer.innerHTML = `
                            <div class="glass-card rounded-xl p-8 text-center text-white">
                                <i class="fas fa-search fa-3x mb-4 opacity-70"></i>
                                <h3 class="text-xl font-semibold mb-2">Aucun résultat trouvé pour "${query}"</h3>
                                <p>Veuillez essayer avec d'autres termes de recherche.</p>
                            </div>
                        `;
                        return;
                    }
                    
                    let resultsHTML = '';
                    let delay = 1;
                    
                    data.forEach(hotel => {
                        const delay_class = "delay-" + ((delay % 5) + 1) * 100;
                        const features = hotel.features ? displayFeatures(hotel.features) : '';
                        const stars = displayStars(hotel.rating || 3);
                        
                        resultsHTML += `
                            <div class="hotel-card glass-card rounded-xl overflow-hidden animate-fade-in ${delay_class}" data-hotel-id="${hotel.id}">
                                <div class="flex flex-col md:flex-row">
                                    <div class="hotel-image md:w-1/3 h-64 md:h-auto overflow-hidden">
                                        <img src="${hotel.image_url}" 
                                             alt="${hotel.title}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-6 flex-1 flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-start">
                                                <h3 class="text-xl font-bold text-white mb-2">${hotel.title}</h3>
                                                <div class="text-2xl font-bold text-white">${formatPrice(hotel.price)} <span class="text-sm font-normal opacity-80">/ nuit</span></div>
                                            </div>
                                            <div class="flex items-center mb-3">
                                                <div class="flex mr-2">
                                                    ${stars}
                                                </div>
                                                <span class="text-white/80">(${hotel.reviews_count || 0} avis)</span>
                                            </div>
                                            <p class="text-white/90 mb-3">
                                                <i class="fas fa-map-marker-alt mr-2 text-primary-300"></i>
                                                ${hotel.location}
                                            </p>
                                            ${features}
                                        </div>
                                        <div class="mt-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                                            <a href="/development/app/views/hotel-details.php?id=${hotel.id}" class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white rounded-lg hover:bg-white/30 transition text-center">
                                                <i class="fas fa-info-circle mr-2"></i>Détails
                                            </a>
                                            <button onclick="showBookingForm(${hotel.id}, '${hotel.title.replace(/'/g, "\\'")}', ${hotel.price})" 
                                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition shadow-lg hover:shadow-xl text-center">
                                                <i class="fas fa-calendar-check mr-2"></i>Réserver maintenant
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        delay++;
                    });
                    
                    resultsContainer.innerHTML = resultsHTML;
                    
                } catch (error) {
                    console.error('Error fetching search results:', error);
                    resultsContainer.innerHTML = `
                        <div class="glass-card rounded-xl p-8 text-center text-white">
                            <i class="fas fa-exclamation-triangle fa-3x mb-4 text-red-500"></i>
                            <h3 class="text-xl font-semibold mb-2">Une erreur s'est produite</h3>
                            <p>Impossible de se connecter à l'API de recherche. Veuillez réessayer plus tard.</p>
                        </div>
                    `;
                }
            }
            
            // Check if there's a query parameter and fetch results
            const urlParams = new URLSearchParams(window.location.search);
            const queryParam = urlParams.get('q');
            if (queryParam) {
                searchInput.value = queryParam;
                fetchSearchResults(queryParam);
            } else {
                resultsContainer.innerHTML = `
                    <div class="glass-card rounded-xl p-8 text-center text-white">
                        <i class="fas fa-search fa-3x mb-4 opacity-70"></i>
                        <h3 class="text-xl font-semibold mb-2">Commencez votre recherche</h3>
                        <p>Entrez le nom d'un hôtel ou d'une destination pour trouver des résultats.</p>
                    </div>
                `;
            }
            
            // Handle form submission
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const query = searchInput.value.trim();
                if (query) {
                    // Update URL with search query
                    const newUrl = `${window.location.pathname}?q=${encodeURIComponent(query)}`;
                    window.history.pushState({ path: newUrl }, '', newUrl);
                    fetchSearchResults(query);
                }
            });

        // Booking modal functions
        function showBookingModal() {
            document.getElementById('bookingModal').classList.remove('hidden');
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
        }

        function showBookingForm(hotelId, hotelTitle, hotelPrice) {
            // Set hotel details in the modal
            document.getElementById('modal-hotel-id').value = hotelId;
            document.getElementById('modal-hotel-title').textContent = hotelTitle;
            document.getElementById('modal-hotel-price').textContent = hotelPrice;
            
            // Set default dates
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const dayAfterTomorrow = new Date(today);
            dayAfterTomorrow.setDate(dayAfterTomorrow.getDate() + 2);
            
            document.getElementById('modal-check-in').value = formatDate(tomorrow);
            document.getElementById('modal-check-out').value = formatDate(dayAfterTomorrow);
            
            // Reset form
            document.getElementById('modal-room-type').value = 'standard';
            document.getElementById('modal-guests').value = '2';
            document.getElementById('terms-check').checked = false;
            
            // Show booking details form, hide payment form
            document.getElementById('booking-details-form').classList.remove('hidden');
            document.getElementById('payment-form').classList.add('hidden');
            
            // Show confirm button, hide payment buttons
            document.getElementById('confirm-btn').classList.remove('hidden');
            document.getElementById('pay-now-btn').classList.add('hidden');
            document.getElementById('pay-later-btn').classList.add('hidden');
            
            // Update booking summary
            updateBookingSummary();
            
            // Show modal
            showBookingModal();
        }

        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function updateBookingSummary() {
            const hotelPrice = parseFloat(document.getElementById('modal-hotel-price').textContent);
            const roomType = document.getElementById('modal-room-type').value;
            const checkIn = new Date(document.getElementById('modal-check-in').value);
            const checkOut = new Date(document.getElementById('modal-check-out').value);
            
            // Calculate number of nights
            const nights = Math.round((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            
            // Calculate room price based on type
            let roomMultiplier = 1;
            let roomTypeName = 'Standard';
            
            switch(roomType) {
                case 'deluxe':
                    roomMultiplier = 1.5;
                    roomTypeName = 'Deluxe';
                    break;
                case 'suite':
                    roomMultiplier = 2;
                    roomTypeName = 'Suite';
                    break;
                case 'family':
                    roomMultiplier = 1.8;
                    roomTypeName = 'Familiale';
                    break;
            }
            
            const pricePerNight = hotelPrice * roomMultiplier;
            const subtotal = pricePerNight * nights;
            const tax = subtotal * 0.07; // 7% tax
            const total = subtotal + tax;
            
            // Update summary
            document.getElementById('booking-dates').textContent = `Séjour: ${formatLocalDate(checkIn)} - ${formatLocalDate(checkOut)}`;
            document.getElementById('booking-nights').textContent = `Durée: ${nights} nuit${nights > 1 ? 's' : ''}`;
            document.getElementById('booking-room-type').textContent = `Type de chambre: ${roomTypeName}`;
            document.getElementById('booking-price-per-night').textContent = `Prix par nuit: ${pricePerNight.toFixed(2)} DT`;
            document.getElementById('booking-subtotal').textContent = `Sous-total: ${subtotal.toFixed(2)} DT`;
            document.getElementById('booking-tax').textContent = `Taxes (7%): ${tax.toFixed(2)} DT`;
            document.getElementById('booking-total').textContent = `Total: ${total.toFixed(2)} DT`;
            
            // Update hidden total price field
            document.getElementById('total-price').value = total.toFixed(2);
        }

        function formatLocalDate(date) {
            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }

        function proceedToPayment() {
            // Check if terms are accepted
            if (!document.getElementById('terms-check').checked) {
                alert('Veuillez accepter les conditions générales de vente.');
                return;
            }
            
            // Show payment form
            document.getElementById('payment-form').classList.remove('hidden');
            
            // Hide confirm button, show payment buttons
            document.getElementById('confirm-btn').classList.add('hidden');
            document.getElementById('pay-now-btn').classList.remove('hidden');
            document.getElementById('pay-later-btn').classList.remove('hidden');
        }

        function processPayment() {
            // Set payment status to confirmed
            document.getElementById('payment-status').value = 'confirmed';
            
            // Submit the form
            document.getElementById('booking-form').submit();
        }

        function payLater() {
            // Set payment status to pending
            document.getElementById('payment-status').value = 'pending';
            
            // Submit the form
            document.getElementById('booking-form').submit();
        }

        // Add event listeners for booking form fields
        document.addEventListener('DOMContentLoaded', function() {
            const modalCheckIn = document.getElementById('modal-check-in');
            const modalCheckOut = document.getElementById('modal-check-out');
            const modalRoomType = document.getElementById('modal-room-type');
            
            if (modalCheckIn && modalCheckOut && modalRoomType) {
                modalCheckIn.addEventListener('change', updateBookingSummary);
                modalCheckOut.addEventListener('change', updateBookingSummary);
                modalRoomType.addEventListener('change', updateBookingSummary);
                
                // Set minimum date for check-out based on check-in
                modalCheckIn.addEventListener('change', function() {
                    const checkInDate = new Date(this.value);
                    const nextDay = new Date(checkInDate);
                    nextDay.setDate(nextDay.getDate() + 1);
                    
                    modalCheckOut.min = formatDate(nextDay);
                    
                    // If check-out date is before or equal to check-in date, update it
                    if (new Date(modalCheckOut.value) <= checkInDate) {
                        modalCheckOut.value = formatDate(nextDay);
                    }
                      {
                        modalCheckOut.value = formatDate(nextDay);
                    }
                    
                    updateBookingSummary();
                });
            }
        });
    </script>
    <script src="/development/public/js/bg.js"></script>
</body>
</html>
