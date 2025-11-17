<?php
session_start();
require_once __DIR__.'/../../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$update_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $birthday = $_POST['birthday'];
    $card_name = $_POST['card_name'];
    $card_expire = $_POST['card_expire'];
    

    $update_sql = "UPDATE users SET 
                  firstname = '$firstname', 
                  lastname = '$lastname', 
                  fullname = '$firstname $lastname', 
                  email = '$email', 
                  birthday = '$birthday', 
                  card_name = '$card_name', 
                  card_expire = '$card_expire'
                  WHERE id = $user_id";
    
    if ($conn->query($update_sql) === TRUE) {

        $_SESSION['user_name'] = $firstname;
        $update_message = '<div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">Vos informations ont été mises à jour avec succès!</div>';
    } else {
        $update_message = '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">Erreur: ' . $conn->error . '</div>';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $password_sql = "SELECT password FROM users WHERE id = $user_id";
    $password_result = $conn->query($password_sql);
    $user_password = $password_result->fetch_assoc()['password'];
    
    if (password_verify($current_password, $user_password)) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
            
            if ($conn->query($update_password_sql) === TRUE) {
                $update_message = '<div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">Votre mot de passe a été mis à jour avec succès!</div>';
            } else {
                $update_message = '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">Erreur: ' . $conn->error . '</div>';
            }
        } else {
            $update_message = '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">Les nouveaux mots de passe ne correspondent pas.</div>';
        }
    } else {
        $update_message = '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">Le mot de passe actuel est incorrect.</div>';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];

    $cancel_sql = "UPDATE bookings SET status = 'cancelled' WHERE id = $booking_id AND user_id = $user_id";
    
    if ($conn->query($cancel_sql) === TRUE) {
        $update_message = '<div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">Votre réservation a été annulée avec succès!</div>';
    } else {
        $update_message = '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">Erreur: ' . $conn->error . '</div>';
    }
}


$review_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $hotel_id = $_POST['hotel_id'];
    $booking_id = $_POST['booking_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    

    $check_sql = "SELECT id FROM reviews WHERE user_id = $user_id AND booking_id = $booking_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {

        $review_id = $check_result->fetch_assoc()['id'];
        $update_sql = "UPDATE reviews SET rating = $rating, comment = '$comment', review_date = NOW() WHERE id = $review_id";
        
        if ($conn->query($update_sql) === TRUE) {
            $review_message = '<div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">Votre avis a été mis à jour avec succès!</div>';
        } else {
            $review_message = '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">Erreur: ' . $conn->error . '</div>';
        }
    } else {

        $insert_sql = "INSERT INTO reviews (user_id, hotel_id, booking_id, rating, comment) VALUES ($user_id, $hotel_id, $booking_id, $rating, '$comment')";
        
        if ($conn->query($insert_sql) === TRUE) {

            $update_hotel_sql = "UPDATE hotels SET reviews_count = reviews_count + 1 WHERE id = $hotel_id";
            $conn->query($update_hotel_sql);
            
            $review_message = '<div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">Votre avis a été publié avec succès!</div>';
        } else {
            $review_message = '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">Erreur: ' . $conn->error . '</div>';
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_now'])) {

    $booking_id = $_POST['booking_id'];

    $query = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE id = ?");
    $query->bind_param("i", $booking_id);
    $res = $query->execute();


}

$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_sql);

if ($user_result->num_rows == 0) {
    session_destroy();
    header("Location: signin.php");
    exit();
}

$user = $user_result->fetch_assoc();

$bookings_sql = "SELECT b.*, h.title as hotel_name, h.image_url, h.location 
                FROM bookings b 
                JOIN hotels h ON b.hotel_id = h.id 
                WHERE b.user_id = $user_id 
                ORDER BY b.booking_date DESC";
$bookings_result = $conn->query($bookings_sql);

$reviews_sql = "SELECT r.*, h.title as hotel_name, h.image_url 
               FROM reviews r 
               JOIN hotels h ON r.hotel_id = h.id 
               WHERE r.user_id = $user_id 
               ORDER BY r.review_date DESC";
$reviews_result = $conn->query($reviews_sql);

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

function formatDate($date) {
    $date_obj = new DateTime($date);
    return $date_obj->format('d M Y');
}

function getStatusClass($status) {
    switch ($status) {
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        case 'no_show':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function translateStatus($status) {
    switch ($status) {
        case 'confirmed':
            return 'Confirmée';
        case 'pending':
            return 'En attente';
        case 'cancelled':
            return 'Annulée';
        case 'completed':
            return 'Terminée';
        case 'no_show':
            return 'Non présenté';
        default:
            return $status;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Mon Profil - Bayatni.tn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#e0926e',
                            100: '#bf704d',
                            200: '#c76d44',
                            300: '#c46133',
                            400: '#cc5e2b',
                            500: '#b85325',
                            600: '#c4541f',
                            700: '#d45215',
                            800: '#cf4a0c',
                            900: '#d64806',
                        },
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        serif: ['Merriweather', 'serif'],
                    },
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/development/public/css/profile.css">
</head>

<body class="min-h-screen text-gray-800">

    <?php include __DIR__."/../../../includes/header.php"; ?>
    
    <?php   
    if ( isset($res)) {
        if ($res === true) {
            echo '<div style="width:20%; justify-self:center; text-align:center;" class="p-4 mt-5 text-sm text-green-700 bg-green-100 rounded-lg">Réservation confirmée et payée!</div>';
        } else {
            echo '<div style="width:40%; justify-self:center; text-align:center;" class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">Erreur: ' . $query->error . '</div>';
        }
    }    
    ?>

    <div id="background-container">
        <div class="bg-layer" id="bg1"></div>
        <div class="bg-layer" id="bg2"></div>
    </div>

    <main class="pt-24 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="container mx-auto">
            <?php echo $update_message; ?>
            
            <?php echo $review_message; ?>

            <div class="glass-card rounded-2xl p-8 mb-8 text-white">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                    <div class="w-32 h-32 rounded-full bg-primary-600 flex items-center justify-center text-white text-4xl font-bold">
                        <?php echo strtoupper(substr($user['firstname'], 0, 1) . substr($user['lastname'], 0, 1)); ?>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($user['fullname']); ?></h1>
                        <p class="text-white/80 mb-4">
                            <i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($user['email']); ?>
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                                <div class="text-sm text-white/70">Membre depuis</div>
                                <div class="font-semibold"><?php echo formatDate($user['created_at']); ?></div>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                                <div class="text-sm text-white/70">Réservations</div>
                                <div class="font-semibold"><?php echo $bookings_result->num_rows; ?></div>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                                <div class="text-sm text-white/70">Avis</div>
                                <div class="font-semibold"><?php echo $reviews_result->num_rows; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-6 border-b border-white/20">
                <div class="flex overflow-x-auto">
                    <button class="tab-btn tab-active px-6 py-3 font-medium text-white/80 hover:text-white transition" data-tab="bookings">
                        <i class="fas fa-calendar-check mr-2"></i>Mes Réservations
                    </button>
                    <button class="tab-btn px-6 py-3 font-medium text-white/80 hover:text-white transition" data-tab="reviews">
                        <i class="fas fa-star mr-2"></i>Mes Avis
                    </button>
                    <button class="tab-btn px-6 py-3 font-medium text-white/80 hover:text-white transition" data-tab="account">
                        <i class="fas fa-user-cog mr-2"></i>Paramètres du Compte
                    </button>
                </div>
            </div>
            
            <div class="tab-content active" id="bookings-tab">
                <?php if ($bookings_result->num_rows > 0): ?>
                    <div class="space-y-6">
                        <?php while($booking = $bookings_result->fetch_assoc()): ?>
                            <div class="glass-card rounded-xl overflow-hidden">
                                <div class="flex flex-col md:flex-row">
                                    <div class="md:w-1/4 h-48 md:h-auto overflow-hidden">
                                        <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($booking['hotel_name']); ?>"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-6 flex-1">
                                        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                                            <div>
                                                <h3 class="text-xl font-bold text-white mb-2"><?php echo htmlspecialchars($booking['hotel_name']); ?></h3>
                                                <p class="text-white/80 mb-3">
                                                    <i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($booking['location']); ?>
                                                </p>
                                                <div class="flex flex-wrap gap-4 mb-4">
                                                    <div>
                                                        <div class="text-sm text-white/70">Arrivée</div>
                                                        <div class="font-semibold text-white"><?php echo formatDate($booking['check_in']); ?></div>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm text-white/70">Départ</div>
                                                        <div class="font-semibold text-white"><?php echo formatDate($booking['check_out']); ?></div>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm text-white/70">Voyageurs</div>
                                                        <div class="font-semibold text-white"><?php echo $booking['guests']; ?> personne<?php echo $booking['guests'] > 1 ? 's' : ''; ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?php echo getStatusClass($booking['status']); ?>">
                                                    <?php echo translateStatus($booking['status']); ?>

                                                </span>

                                                <div class="mt-2 text-xl font-bold text-white"><?php echo number_format($booking['total_price'], 0); ?> DT</div>
                                                <div class="text-xs text-white/70">Réservé le <?php echo formatDate($booking['booking_date']); ?></div>
                                                <?php if ($booking['status'] == 'pending'): ?>
                                                <form method="POST" action="profile.php" id="booking-form-<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="pay_now" value="1">
                                                    <button type="submit" style="position:absolute; bottom:10%;" class="pay-btn">
                                                        <span class="btn-text">Pay Now</span>
                                                            <div class="icon-container">
                                                                <svg viewBox="0 0 24 24" class="icon card-icon">
                                                                <path
                                                                    d="M20,8H4V6H20M20,18H4V12H20M20,4H4C2.89,4 2,4.89 2,6V18C2,19.11 2.89,20 4,20H20C21.11,20 22,19.11 22,18V6C22,4.89 21.11,4 20,4Z"
                                                                    fill="currentColor"
                                                                ></path>
                                                                </svg>
                                                                <svg viewBox="0 0 24 24" class="icon payment-icon">
                                                                <path
                                                                    d="M2,17H22V21H2V17M6.25,7H9V6H6V3H18V6H15V7H17.75L19,17H5L6.25,7M9,10H15V8H9V10M9,13H15V11H9V13Z"
                                                                    fill="currentColor"
                                                                ></path>
                                                                </svg>
                                                                <svg viewBox="0 0 24 24" class="icon dollar-icon">
                                                                <path
                                                                    d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"
                                                                    fill="currentColor"
                                                                ></path>
                                                                </svg>

                                                                <svg viewBox="0 0 24 24" class="icon wallet-icon default-icon">
                                                                <path
                                                                    d="M21,18V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H19A2,2 0 0,1 21,5V6H12C10.89,6 10,6.9 10,8V16A2,2 0 0,0 12,18M12,16H22V8H12M16,13.5A1.5,1.5 0 0,1 14.5,12A1.5,1.5 0 0,1 16,10.5A1.5,1.5 0 0,1 17.5,12A1.5,1.5 0 0,1 16,13.5Z"
                                                                    fill="currentColor"
                                                                ></path>
                                                                </svg>

                                                                <svg viewBox="0 0 24 24" class="icon check-icon">
                                                                <path
                                                                    d="M9,16.17L4.83,12L3.41,13.41L9,19L21,7L19.59,5.59L9,16.17Z"
                                                                    fill="currentColor"
                                                                ></path>
                                                                </svg>
                                                            </div>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                            
                                        </div>
                                        
                                        <div class="mt-4 flex flex-wrap gap-3">
                                            <?php if ($booking['status'] == 'confirmed'): ?>
                                                <form method="POST" action="profile.php" class="inline">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="cancel_booking" value="1">
                                                    <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition">
                                                        <i class="fas fa-times mr-1"></i>Annuler
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($booking['status'] == 'completed' || $booking['status'] == 'confirmed'): ?>
                                                <button onclick="showReviewModal(<?php echo $booking['hotel_id']; ?>, <?php echo $booking['id']; ?>, '<?php echo htmlspecialchars(addslashes($booking['hotel_name'])); ?>')" 
                                                        class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm transition">
                                                    <i class="fas fa-star mr-1"></i>Laisser un avis
                                                </button>
                                            <?php endif; ?>
                                            
                                            <a href="hotel-details.php?id=<?php echo $booking['hotel_id']; ?>" class="px-3 py-1.5 bg-white/20 backdrop-blur-sm text-white rounded-lg text-sm hover:bg-white/30 transition">
                                                <i class="fas fa-info-circle mr-1"></i>Détails de l'hôtel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="glass-card rounded-xl p-8 text-center text-white">
                        <i class="fas fa-calendar-times fa-3x mb-4 opacity-70"></i>
                        <h3 class="text-xl font-semibold mb-2">Aucune réservation trouvée</h3>
                        <p class="mb-4">Vous n'avez pas encore effectué de réservation.</p>
                        <a href="booking.php" class="inline-block px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition shadow-lg hover:shadow-xl">
                            <i class="fas fa-search mr-2"></i>Trouver un hôtel
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-content" id="reviews-tab">
                <?php if ($reviews_result->num_rows > 0): ?>
                    <div class="space-y-6">
                        <?php while($review = $reviews_result->fetch_assoc()): ?>
                            <div class="glass-card rounded-xl overflow-hidden">
                                <div class="flex flex-col md:flex-row">
                                    <div class="md:w-1/5 h-32 md:h-auto overflow-hidden">
                                        <img src="<?php echo htmlspecialchars($review['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($review['hotel_name']); ?>"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-6 flex-1">
                                        <div class="flex justify-between items-start">
                                            <h3 class="text-xl font-bold text-white mb-2"><?php echo htmlspecialchars($review['hotel_name']); ?></h3>
                                            <div class="text-xs text-white/70"><?php echo formatDate($review['review_date']); ?></div>
                                        </div>
                                        <div class="flex mb-3">
                                            <?php echo displayStars($review['rating']); ?>
                                        </div>
                                        <p class="text-white/90"><?php echo htmlspecialchars($review['comment']); ?></p>
                                        <div class="mt-4">
                                            <button onclick="showReviewModal(<?php echo $review['hotel_id']; ?>, <?php echo $review['booking_id']; ?>, '<?php echo htmlspecialchars(addslashes($review['hotel_name'])); ?>', <?php echo $review['rating']; ?>, '<?php echo htmlspecialchars(addslashes($review['comment'])); ?>')" 
                                                    class="px-3 py-1.5 bg-white/20 backdrop-blur-sm text-white rounded-lg text-sm hover:bg-white/30 transition">
                                                <i class="fas fa-edit mr-1"></i>Modifier
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="glass-card rounded-xl p-8 text-center text-white">
                        <i class="fas fa-comment-slash fa-3x mb-4 opacity-70"></i>
                        <h3 class="text-xl font-semibold mb-2">Aucun avis trouvé</h3>
                        <p>Vous n'avez pas encore laissé d'avis sur vos séjours.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-content" id="account-tab">
                <div class="glass-card rounded-xl p-8 text-white">
                    <h2 class="text-2xl font-bold mb-6">Informations personnelles</h2>
                    <form class="space-y-6" method="POST" action="profile.php">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="firstname" class="block mb-2 font-medium">Prénom</label>
                                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" 
                                       class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                            </div>
                            <div>
                                <label for="lastname" class="block mb-2 font-medium">Nom</label>
                                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" 
                                       class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                            </div>
                        </div>
                        
                        <div>
                            <label for="email" class="block mb-2 font-medium">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                        </div>
                        
                        <div>
                            <label for="birthday" class="block mb-2 font-medium">Date de naissance</label>
                            <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>" 
                                   class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                        </div>
                        
                        <h3 class="text-xl font-bold mt-8 mb-4">Informations de paiement</h3>
                        
                        <div>
                            <label for="card_number" class="block mb-2 font-medium">Numéro de carte</label>
                            <input type="text" id="card_number" name="card_number" value="<?php echo substr($user['card_number'], 0, 4) . ' **** **** ' . substr($user['card_number'], -4); ?>" 
                                   class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white" readonly>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="card_name" class="block mb-2 font-medium">Nom sur la carte</label>
                                <input type="text" id="card_name" name="card_name" value="<?php echo htmlspecialchars($user['card_name']); ?>" 
                                       class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                            </div>
                            <div>
                                <label for="card_expire" class="block mb-2 font-medium">Date d'expiration</label>
                                <input type="text" id="card_expire" name="card_expire" value="<?php echo htmlspecialchars($user['card_expire']); ?>" 
                                       class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition shadow-lg hover:shadow-xl">
                                <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                    
                    <h3 class="text-xl font-bold mt-12 mb-4">Sécurité</h3>
                    
                    <form class="space-y-6" method="POST" action="profile.php">
                        <input type="hidden" name="update_password" value="1">
                        <div>
                            <label for="current_password" class="block mb-2 font-medium">Mot de passe actuel</label>
                            <input type="password" id="current_password" name="current_password" 
                                   class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="new_password" class="block mb-2 font-medium">Nouveau mot de passe</label>
                                <input type="password" id="new_password" name="new_password" 
                                       class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                            </div>
                            <div>
                                <label for="confirm_password" class="block mb-2 font-medium">Confirmer le mot de passe</label>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                       class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white">
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition shadow-lg hover:shadow-xl">
                                <i class="fas fa-key mr-2"></i>Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <div id="reviewModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom glass-card rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-2xl leading-6 font-bold text-white mb-4" id="review-modal-title">
                                Laisser un avis
                            </h3>
                            <form method="POST" action="profile.php" id="review-form">
                                <input type="hidden" name="hotel_id" id="review-hotel-id">
                                <input type="hidden" name="booking_id" id="review-booking-id">
                                <input type="hidden" name="submit_review" value="1">
                                
                                <div class="mb-4">
                                    <h4 id="review-hotel-name" class="text-lg font-semibold text-white"></h4>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-white mb-1">Note</label>
                                    <div class="flex gap-2 text-2xl">
                                        <span class="star-rating cursor-pointer" data-rating="1"><i class="far fa-star text-yellow-400"></i></span>
                                        <span class="star-rating cursor-pointer" data-rating="2"><i class="far fa-star text-yellow-400"></i></span>
                                        <span class="star-rating cursor-pointer" data-rating="3"><i class="far fa-star text-yellow-400"></i></span>
                                        <span class="star-rating cursor-pointer" data-rating="4"><i class="far fa-star text-yellow-400"></i></span>
                                        <span class="star-rating cursor-pointer" data-rating="5"><i class="far fa-star text-yellow-400"></i></span>
                                    </div>
                                    <input type="hidden" name="rating" id="review-rating" value="5">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="review-comment" class="block text-sm font-medium text-white mb-1">Commentaire</label>
                                    <textarea id="review-comment" name="comment" rows="4" 
                                              class="w-full p-3 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 focus:outline-none focus:ring-2 focus:ring-primary-500 text-white"
                                              placeholder="Partagez votre expérience..."></textarea>
                                </div>
                                
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="closeReviewModal()" 
                                            class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white rounded-lg hover:bg-white/30 transition">
                                        Annuler
                                    </button>
                                    <button type="submit" 
                                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition shadow-lg hover:shadow-xl">
                                        Publier l'avis
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>  

    <?php include __DIR__."/../../../includes/footer.php"; ?>

    <script>

        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabId = btn.getAttribute('data-tab');
                
                tabBtns.forEach(b => b.classList.remove('tab-active'));
                tabContents.forEach(c => c.classList.remove('active'));
                btn.classList.add('tab-active');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });
        
        function showReviewModal(hotelId, bookingId, hotelName, rating = 5, comment = '') {
            document.getElementById('review-hotel-id').value = hotelId;
            document.getElementById('review-booking-id').value = bookingId;
            document.getElementById('review-hotel-name').textContent = hotelName;
            document.getElementById('review-rating').value = rating;
            document.getElementById('review-comment').value = comment;
            
            updateStars(rating);
            
            document.getElementById('reviewModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeReviewModal() {
            document.getElementById('reviewModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        const stars = document.querySelectorAll('.star-rating');
        
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = parseInt(star.getAttribute('data-rating'));
                document.getElementById('review-rating').value = rating;
                updateStars(rating);
            });
            
            star.addEventListener('mouseover', () => {
                const rating = parseInt(star.getAttribute('data-rating'));
                highlightStars(rating);
            });
            
            star.addEventListener('mouseout', () => {
                const currentRating = parseInt(document.getElementById('review-rating').value);
                updateStars(currentRating);
            });
        });
        
        function updateStars(rating) {
            stars.forEach(star => {
                const starRating = parseInt(star.getAttribute('data-rating'));
                if (starRating <= rating) {
                    star.innerHTML = '<i class="fas fa-star text-yellow-400"></i>';
                } else {
                    star.innerHTML = '<i class="far fa-star text-yellow-400"></i>';
                }
            });
        }
        
        function highlightStars(rating) {
            stars.forEach(star => {
                const starRating = parseInt(star.getAttribute('data-rating'));
                if (starRating <= rating) {
                    star.innerHTML = '<i class="fas fa-star text-yellow-400"></i>';
                } else {
                    star.innerHTML = '<i class="far fa-star text-yellow-400"></i>';
                }
            });
        }


    </script>
    <script src="/development/public/js/bg.js"></script>
</body>
</html>
<?php
$conn->close();
?>
