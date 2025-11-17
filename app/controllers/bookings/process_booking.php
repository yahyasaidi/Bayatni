<?php
session_start();
require_once __DIR__ .'/../../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /development/app/views/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /development/app/views/index.php");
    exit();
}

$userId = $_SESSION['user_id'];

$hotelId = isset($_POST['hotel_id']) ? intval($_POST['hotel_id']) : 0;
$checkIn = isset($_POST['check_in']) ? $_POST['check_in'] : '';
$checkOut = isset($_POST['check_out']) ? $_POST['check_out'] : '';
$guests = isset($_POST['guests']) ? intval($_POST['guests']) : 0;
$roomType = isset($_POST['room_type']) ? $_POST['room_type'] : '';
$totalPrice = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0;
$paymentStatus = isset($_POST['payment_status']) ? $_POST['payment_status'] : 'pending';
$paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

$bookingStatus = ($paymentStatus === 'confirmed') ? 'confirmed' : 'pending';

if (!$hotelId || !$checkIn || !$checkOut || !$guests || !$roomType || !$totalPrice) {
    $_SESSION['error_message'] = "Tous les champs sont obligatoires.";
    header("Location: /development/app/views/search-results.php?error=missing_fields");
    exit();
}

$query = "INSERT INTO bookings (user_id, hotel_id, check_in, check_out, guests, room_type, total_price, status) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("iissisds", $userId, $hotelId, $checkIn, $checkOut, $guests, $roomType, $totalPrice, $bookingStatus);

if ($stmt->execute()) {
    $bookingId = $conn->insert_id;
    
    if ($bookingStatus === 'confirmed') {
        $_SESSION['success_message'] = "Réservation confirmée et payée! Numéro de réservation: " . $bookingId;
        $_SESSION['payment_status'] = 'confirmed';
    } else {
        $_SESSION['success_message'] = "Consulter votre profil pour Finaliser , Numéro de réservation: " . $bookingId;
        $_SESSION['payment_status'] = 'pending';
    }
    
    header("Location: /development/app/controllers/bookings/booking-confirmation.php?id=$bookingId");
    exit();
} else {
    $_SESSION['error_message'] = "Une erreur s'est produite lors de la réservation. Veuillez réessayer.";
    header("Location: /development/app/views/search-results.php?error=db_error");
    exit();
}
