<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: bookings.php');
    exit;
}

$id = intval($_GET['id']);

$query = "SELECT b.*, u.fullname as guest_name, u.email as guest_email, u.card_number, u.card_name, u.card_expire, u.card_cvc,
                 h.title as hotel_name, h.location as hotel_location, h.image_url as hotel_image
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN hotels h ON b.hotel_id = h.id
          WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: bookings.php');
    exit;
}

$booking = $result->fetch_assoc();

$checkInDate = new DateTime($booking['check_in']);
$checkOutDate = new DateTime($booking['check_out']);
$interval = $checkInDate->diff($checkOutDate);
$nights = $interval->days;

$roomTypes = [
    'standard' => 'Standard Room',
    'deluxe' => 'Deluxe Room',
    'suite' => 'Suite',
    'family' => 'Family Room',
    'presidential' => 'Presidential Suite'
];
$roomTypeName = $roomTypes[$booking['room_type']] ?? ucfirst($booking['room_type']);

if (isset($_POST['action']) && isset($_POST['new_status'])) {
    $newStatus = $_POST['new_status'];
    $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
    
    if (in_array($newStatus, $validStatuses)) {
        $updateQuery = "UPDATE bookings SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $newStatus, $id);
        
        if ($stmt->execute()) {
            $booking['status'] = $newStatus;
            $statusUpdateSuccess = true;
        } else {
            $statusUpdateError = "Error updating status: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Hotel Booking Admin</title>
    <link rel="stylesheet" href="../../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
    <?php include __DIR__ .'/../../../includes/sidebar.php'; ?>

        <main class="main-content">
        <?php include __DIR__ .'/../../../includes/header.php'; ?>

            <div class="dashboard-content">
                <div class="page-header">
                    <h1>Booking Details</h1>
                    <div class="header-actions">
                        <a href="booking_edit.php?id=<?php echo $id; ?>" class="secondary-btn">
                            <i class="fas fa-edit"></i> Edit Booking
                        </a>
                        <a href="bookings.php" class="secondary-btn">
                            <i class="fas fa-arrow-left"></i> Back to Bookings
                        </a>
                    </div>
                </div>

                <?php if (isset($statusUpdateSuccess)): ?>
                <div class="alert alert-success">
                    Booking status updated successfully!
                </div>
                <?php endif; ?>

                <?php if (isset($statusUpdateError)): ?>
                <div class="alert alert-danger">
                    <?php echo $statusUpdateError; ?>
                </div>
                <?php endif; ?>

                <div class="booking-details-container">
                    <div class="booking-header">
                        <div class="booking-id">
                            <h2>Booking #BK-<?php echo $id; ?></h2>
                        </div>
                        <span class="status-badge <?php echo strtolower($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span>
                    </div>
                    <form action="booking_view.php?id=<?php echo $id; ?>" method="POST" class="status-change-form">
                    <div class="booking-actions">
                            <select name="new_status" class="status-select">
                                <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <button type="submit" name="action" value="change_status" class="secondary-btn">Update Status</button>
                            <a href="booking_email.php?id=<?php echo $id; ?>">  <button class="secondary-btn">Send Email</button>   </a>
                            <a href="bookings.php?action=delete&id=<?php echo $id; ?>" onclick="return confirm('Are you sure you want to delete this booking?');"><button class="secondary-btn">Delete</button></a>
                    </div>   
                    </form>

                    <div class="booking-details-grid">
                        <div class="booking-detail-card">
                            <h3>Guest Information</h3>
                            <div class="guest-info">
                                <div class="guest-details">
                                    <h4 style="text-transform: capitalize;"><?php echo htmlspecialchars($booking['guest_name']); ?></h4>
                                    <a href="user_view.php?id=<?php echo $booking['user_id']; ?>" class="view-profile-link">View Full Profile</a>
                                </div>
                            </div>
                            <div class="detail-divider" style="margin-bottom:20px;"></div>
                            <div class="payment-info">
                                <h4>Payment Information</h4>
                                <div class="detail-item">
                                    <span class="detail-label">Card Number:</span>
                                    <span class="detail-value">**** **** **** <?php echo substr($booking['card_number'], -4); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Card Holder:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($booking['card_name']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Expiration:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($booking['card_expire']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Amount:</span>
                                    <span class="detail-value price"><?php echo number_format($booking['total_price'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="booking-detail-card" style="margin-top:20px;">
                            <h3>Hotel Information</h3>
                            <div class="hotel-info">
                                <div class="hotel-image">
                                    <img src="<?php echo htmlspecialchars($booking['hotel_image']); ?>" alt="<?php echo htmlspecialchars($booking['hotel_name']); ?>">
                                </div>
                                <div class="hotel-details">
                                    <h4><?php echo htmlspecialchars($booking['hotel_name']); ?></h4>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($booking['hotel_location']); ?></p>
                                    <a href="hotel_view.php?id=<?php echo $booking['hotel_id']; ?>" class="view-hotel-link">View Hotel Details</a>
                                </div>
                            </div>
                    <div class="booking-sum">
                        <div class="room-info">
                                <h4>Room Information</h4>
                                <div class="detail-item">
                                    <span class="detail-label">Room Type:</span>
                                    <span class="detail-value"><?php echo $roomTypeName; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Guests:</span>
                                    <span class="detail-value"><?php echo $booking['guests']; ?> person(s)</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Check-in:</span>
                                    <span class="detail-value"><?php echo date('F d, Y', strtotime($booking['check_in'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Check-out:</span>
                                    <span class="detail-value"><?php echo date('F d, Y', strtotime($booking['check_out'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value"><?php echo $nights; ?> night(s)</span>
                                </div>
                            </div>

                        <div class="booking-detail-card">
                            <h3>Booking Summary</h3>
                            <div class="booking-summary">
                                <div class="detail-item">
                                    <span class="detail-label">Booking Date:</span>
                                    <span class="detail-value"><?php echo date('F d, Y', strtotime($booking['booking_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Booking Status:</span>
                                    <span class="detail-value"><span class="status-badge <?php echo strtolower($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span></span>
                                </div>
                            </div></div>
                        <div class="price-breakdown">
                                    <h4>Price Breakdown</h4>
                                    <div class="price-item">
                                        <span>Room Rate:</span>
                                        <span>$<?php echo number_format($booking['total_price'] / $nights, 2); ?> x <?php echo $nights; ?> nights</span>
                                    </div>
                                    <div class="price-item">
                                        <span>Room Subtotal:</span>
                                        <span>$<?php echo number_format($booking['total_price'], 2); ?></span>
                                    </div>
                                    <div class="price-item">
                                        <span>Taxes & Fees:</span>
                                        <span>Included</span>
                                    </div>
                                    <div class="price-item total">
                                        <span>Total Amount:</span>
                                        <span>$<?php echo number_format($booking['total_price'], 2); ?></span>
                                    </div>
                                </div>
                        
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('save-notes').addEventListener('click', function() {
                const notes = document.getElementById('booking-notes').value;
                alert('Notes saved successfully!');
            });
        });
    </script>
</body>
</html>
