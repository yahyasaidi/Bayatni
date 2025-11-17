<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$id = intval($_GET['id']);

$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: users.php');
    exit;
}

$user = $result->fetch_assoc();


$activeBookingsQuery = "
    SELECT b.id, b.check_in, b.check_out, b.guests, b.total_price, b.status,
           h.title as hotel_name
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
";
$stmt = $conn->prepare($activeBookingsQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$activeBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


$previousBookingsQuery = "
    SELECT b.id, b.check_in, b.check_out, b.guests, b.total_price, b.status,
           h.title as hotel_name
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
";
$stmt = $conn->prepare($previousBookingsQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$previousBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$reviewsQuery = "
    SELECT r.id, r.rating, r.comment, r.review_date,
           h.title as hotel_name
    FROM reviews r
    JOIN hotels h ON r.hotel_id = h.id
    WHERE r.user_id = ?
    ORDER BY r.review_date DESC
";
$stmt = $conn->prepare($reviewsQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$roles = ['guest', 'hotel-owner', 'admin', 'manager'];
$statuses = ['active', 'inactive', 'pending', 'blocked'];
$role = $roles[$id % count($roles)];
$status = $statuses[$id % count($statuses)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - Hotel Booking Admin</title>
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
                    <h1>User Details</h1>
                    <div class="header-actions">
                        <a href="user_edit.php?id=<?php echo $id; ?>" class="secondary-btn">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                        <a href="users.php" class="secondary-btn">
                            <i class="fas fa-arrow-left"></i> Back to Users
                        </a>
                    </div>
                </div>

                <div class="user-profile-container">
                    <div class="user-profile-header">
                        <div class="user-info">
                            <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
                        </div>
                    </div>

                    <div class="user-details-grid">
                        <div class="user-detail-card">
                            <h3>Personal Information</h3>
                            <div class="detail-item">
                                <span class="detail-label">First Name:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['firstname']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Last Name:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['lastname']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Birthday:</span>
                                <span class="detail-value"><?php echo date('F d, Y', strtotime($user['birthday'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Joined:</span>
                                <span class="detail-value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>

                        <div class="user-detail-card">
                            <h3>Payment Information</h3>
                            <div class="detail-item">
                                <span class="detail-label">Card Number:</span>
                                <span class="detail-value">**** **** **** <?php echo substr($user['card_number'], -4); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Card Name:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['card_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Expiration:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($user['card_expire']); ?></span>
                            </div>
                        </div>

                        <div class="user-detail-card">
                            <h3>Booking Statistics</h3>
                            <div class="detail-item">
                                <span class="detail-label">Active Bookings:</span>
                                <span class="detail-value"><?php echo count($activeBookings); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Previous Bookings:</span>
                                <span class="detail-value"><?php echo count($previousBookings); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total Bookings:</span>
                                <span class="detail-value"><?php echo count($activeBookings) + count($previousBookings); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Reviews:</span>
                                <span class="detail-value"><?php echo count($reviews); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="user-sections">
                        <div class="section">
                            <div class="section-header">
                                <h3>Active Bookings</h3>
                                <a href="bookings.php?user_id=<?php echo $id; ?>" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Hotel</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th>Guests</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activeBookings as $booking): ?>
                                        <tr>
                                            <td>#BK-<?php echo $booking['id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                                            <td><?php echo $booking['guests']; ?></td>
                                            <td><span class="status-badge <?php echo strtolower($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                            <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="booking_view.php?id=<?php echo $booking['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                                                    <a href="booking_edit.php?id=<?php echo $booking['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                                    <a href="booking_delete.php?id=<?php echo $booking['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this booking?');"><i class="fas fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($activeBookings)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No active bookings found</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="section">
                            <div class="section-header">
                                <h3>Reviews</h3>
                                <a href="reviews.php?user_id=<?php echo $id; ?>" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="reviews-container">
                                <?php foreach ($reviews as $review): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div>
                                                <h4><?php echo htmlspecialchars($review['hotel_name']); ?></h4>
                                                <span class="review-date"><?php echo date('M d, Y', strtotime($review['review_date'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review['rating']): ?>
                                                    <i class="fas fa-star"></i  ?>
                                                    <i class="fas fa-star"></i>
                                                <?php elseif ($i - 0.5 <= $review['rating']): ?>
                                                    <i class="fas fa-star-half-alt"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <span><?php echo number_format($review['rating'], 1); ?></span>
                                        </div>
                                    </div>
                                    <div class="review-content">
                                        <p>"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                                    </div>
                                    <div class="review-footer">
                                        <div class="review-actions">
                                            <a href="review_edit.php?id=<?php echo $review['id']; ?>" class="review-action"><i class="fas fa-edit"></i> Edit</a>
                                            <a href="review_delete.php?id=<?php echo $review['id']; ?>" class="review-action" onclick="return confirm('Are you sure you want to delete this review?');"><i class="fas fa-trash"></i> Delete</a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($reviews)): ?>
                                <div class="no-data">No reviews found for this user</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
