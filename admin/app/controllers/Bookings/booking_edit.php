<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();

$errors = [];
$success = false;
$booking = null;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: bookings.php');
    exit;
}

$id = intval($_GET['id']);

$query = "SELECT b.*, u.fullname as guest_name, h.title as hotel_name, h.price as hotel_price
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

$usersQuery = "SELECT id, fullname FROM users ORDER BY fullname";
$usersResult = $conn->query($usersQuery);
$users = [];

if ($usersResult) {
    while ($row = $usersResult->fetch_assoc()) {
        $users[] = $row;
    }
}

$hotelsQuery = "SELECT id, title, price FROM hotels ORDER BY title";
$hotelsResult = $conn->query($hotelsQuery);
$hotels = [];

if ($hotelsResult) {
    while ($row = $hotelsResult->fetch_assoc()) {
        $hotels[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = intval($_POST['user_id'] ?? 0);
    $hotel_id = intval($_POST['hotel_id'] ?? 0);
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $guests = intval($_POST['guests'] ?? 0);
    $room_type = $_POST['room_type'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    $payment_method = $_POST['payment_method'] ?? '';
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    $hotelPrice = 0;
    foreach ($hotels as $hotel) {
        if ($hotel['id'] == $hotel_id) {
            $hotelPrice = $hotel['price'];
            break;
        }
    }
    
    $checkInDate = new DateTime($check_in);
    $checkOutDate = new DateTime($check_out);
    $interval = $checkInDate->diff($checkOutDate);
    $nights = $interval->days;
    $roomMultiplier = 1;

    switch ($room_type) {
        case 'standard':
            $roomMultiplier = 1;
            break;
        case 'deluxe':
            $roomMultiplier = 1.5;
            break;
        case 'suite':
            $roomMultiplier = 2;
            break;
        case 'family':
            $roomMultiplier = 2.5;
            break;
        case 'presidential':
            $roomMultiplier = 4;
            break;
    }
    
    $total_price = $hotelPrice * $nights * $roomMultiplier;
    
    if ($user_id <= 0) {
        $errors[] = "Please select a user";
    }
    
    if ($hotel_id <= 0) {
        $errors[] = "Please select a hotel";
    }
    
    if (empty($check_in)) {
        $errors[] = "Check-in date is required";
    }
    
    if (empty($check_out)) {
        $errors[] = "Check-out date is required";
    }
    
    if ($check_in >= $check_out) {
        $errors[] = "Check-out date must be after check-in date";
    }
    
    if ($guests <= 0) {
        $errors[] = "Number of guests must be greater than zero";
    }
    
    if (empty($room_type)) {
        $errors[] = "Room type is required";
    }
    
    if (empty($payment_method)) {
        $errors[] = "Payment method is required";
    }
    
    if (empty($errors)) {
        $query = "UPDATE bookings SET 
                  user_id = ?, 
                  hotel_id = ?, 
                  check_in = ?, 
                  check_out = ?, 
                  guests = ?, 
                  room_type = ?, 
                  status = ?, 
                  payment_method = ?, 
                  total_price = ? 
                  WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iississsdi", $user_id, $hotel_id, $check_in, $check_out, $guests, $room_type, $status, $payment_method, $total_price, $id);
        
        if ($stmt->execute()) {
            $success = true;
            
            $query = "SELECT b.*, u.fullname as guest_name, h.title as hotel_name, h.price as hotel_price
                      FROM bookings b
                      JOIN users u ON b.user_id = u.id
                      JOIN hotels h ON b.hotel_id = h.id
                      WHERE b.id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();
        } else {
            $errors[] = "Error updating booking: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking - Hotel Booking Admin</title>
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
                    <h1>Edit Booking #BK-<?php echo $id; ?></h1>
                    <a href="bookings.php" class="secondary-btn">
                        <i class="fas fa-arrow-left"></i> Back to Bookings
                    </a>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    Booking updated successfully!
                </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form action="booking_edit.php?id=<?php echo $id; ?>" method="POST" class="form">
                            <div class="form-section">
                                <h3 style="margin-bottom:20px;">Guest & Hotel Information</h3>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="user_id">Guest</label>
                                        <select id="user_id" name="user_id" class="form-control" required>
                                            <option value="">Select Guest</option>
                                            <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo $booking['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['fullname']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="hotel_id">Hotel</label>
                                        <select id="hotel_id" name="hotel_id" class="form-control" required>
                                            <option value="">Select Hotel</option>
                                            <?php foreach ($hotels as $hotel): ?>
                                            <option value="<?php echo $hotel['id']; ?>" data-price="<?php echo $hotel['price']; ?>" <?php echo $booking['hotel_id'] == $hotel['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($hotel['title']); ?> - $<?php echo number_format($hotel['price'], 2); ?>/night
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3 style="margin-top:20px; margin-bottom:20px;">Booking Details</h3>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="check_in">Check-in Date</label>
                                        <input type="date" id="check_in" name="check_in" class="form-control" style="padding: 10px 18px; border-radius:5px; border:2px gray;" value="<?php echo htmlspecialchars($booking['check_in']); ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="check_out">Check-out Date</label>
                                        <input type="date" id="check_out" name="check_out" class="form-control" style="padding: 10px 18px; border-radius:5px; border:2px gray;" value="<?php echo htmlspecialchars($booking['check_out']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="guests">Number of Guests</label>
                                        <input type="number" id="guests" name="guests" class="form-control" min="1" value="<?php echo htmlspecialchars($booking['guests']); ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="room_type">Room Type</label>
                                        <select id="room_type" name="room_type" class="form-control" required>
                                            <option value="">Select Room Type</option>
                                            <option value="standard" <?php echo $booking['room_type'] == 'standard' ? 'selected' : ''; ?>>Standard</option>
                                            <option value="deluxe" <?php echo $booking['room_type'] == 'deluxe' ? 'selected' : ''; ?>>Deluxe</option>
                                            <option value="suite" <?php echo $booking['room_type'] == 'suite' ? 'selected' : ''; ?>>Suite</option>
                                            <option value="family" <?php echo $booking['room_type'] == 'family' ? 'selected' : ''; ?>>Family</option>
                                            <option value="presidential" <?php echo $booking['room_type'] == 'presidential' ? 'selected' : ''; ?>>Presidential</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3 style="margin-top:20px; margin-bottom:20px;">Payment & Status</h3>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="payment_method">Payment Method</label>
                                        <select id="payment_method" name="payment_method" class="form-control" required>
                                            <option value="">Select Payment Method</option>
                                            <option value="credit_card" <?php echo $booking['payment_method'] == 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                                            <option value="paypal" <?php echo $booking['payment_method'] == 'paypal' ? 'selected' : ''; ?>>PayPal</option>
                                            <option value="bank_transfer" <?php echo $booking['payment_method'] == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                            <option value="cash" <?php echo $booking['payment_method'] == 'cash' ? 'selected' : ''; ?>>Cash</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="status">Status</label>
                                        <select id="status" name="status" class="form-control" required>
                                            <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3 style="margin-top:20px; margin-bottom:20px;">Price Calculation</h3>
                                <div class="price-calculator">
                                    <div class="price-row">
                                        <span>Base Price:</span>
                                        <span id="base-price">$<?php echo number_format($booking['hotel_price'], 2); ?></span>
                                    </div>
                                    <div class="price-row">
                                        <span>Number of Nights:</span>
                                        <span id="nights-count"><?php 
                                            $checkInDate = new DateTime($booking['check_in']);
                                            $checkOutDate = new DateTime($booking['check_out']);
                                            $interval = $checkInDate->diff($checkOutDate);
                                            echo $interval->days;
                                        ?></span>
                                    </div>
                                    <div class="price-row">
                                        <span>Room Type Adjustment:</span>
                                        <span id="room-adjustment">x<?php 
                                            $multiplier = 1;
                                            switch ($booking['room_type']) {
                                                case 'deluxe': $multiplier = 1.5; break;
                                                case 'suite': $multiplier = 2; break;
                                                case 'family': $multiplier = 2.5; break;
                                                case 'presidential': $multiplier = 4; break;
                                            }
                                            echo number_format($multiplier, 1);
                                        ?></span>
                                    </div>
                                    <div class="price-row total">
                                        <span>Total Price:</span>
                                        <span id="total-price">$<?php echo number_format($booking['total_price'], 2); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="primary-btn">Update Booking</button>
                                <a href="bookings.php" class="secondary-btn">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hotelSelect = document.getElementById('hotel_id');
            const checkInInput = document.getElementById('check_in');
            const checkOutInput = document.getElementById('check_out');
            const roomTypeSelect = document.getElementById('room_type');
            
            const basePriceElement = document.getElementById('base-price');
            const nightsCountElement = document.getElementById('nights-count');
            const roomAdjustmentElement = document.getElementById('room-adjustment');
            const totalPriceElement = document.getElementById('total-price');
            
            // Function to calculate and update price
            function updatePriceCalculation() {
                // Get hotel price
                const selectedHotel = hotelSelect.options[hotelSelect.selectedIndex];
                const basePrice = selectedHotel ? parseFloat(selectedHotel.dataset.price) : 0;
                
                // Calculate nights
                let nights = 0;
                if (checkInInput.value && checkOutInput.value) {
                    const checkIn = new Date(checkInInput.value);
                    const checkOut = new Date(checkOutInput.value);
                    const timeDiff = checkOut.getTime() - checkIn.getTime();
                    nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
                    if (nights < 0) nights = 0;
                }
                
                // Get room type multiplier
                let roomMultiplier = 1;
                switch (roomTypeSelect.value) {
                    case 'deluxe':
                        roomMultiplier = 1.5;
                        break;
                    case 'suite':
                        roomMultiplier = 2;
                        break;
                    case 'family':
                        roomMultiplier = 2.5;
                        break;
                    case 'presidential':
                        roomMultiplier = 4;
                        break;
                }
                
                // Calculate total
                const totalPrice = basePrice * nights * roomMultiplier;
                
                // Update display
                basePriceElement.textContent = '$' + basePrice.toFixed(2);
                nightsCountElement.textContent = nights;
                roomAdjustmentElement.textContent = 'x' + roomMultiplier.toFixed(1);
                totalPriceElement.textContent = '$' + totalPrice.toFixed(2);
            }
            
            // Add event listeners
            hotelSelect.addEventListener('change', updatePriceCalculation);
            checkInInput.addEventListener('change', updatePriceCalculation);
            checkOutInput.addEventListener('change', updatePriceCalculation);
            roomTypeSelect.addEventListener('change', updatePriceCalculation);
        });
    </script>
</body>
</html>
