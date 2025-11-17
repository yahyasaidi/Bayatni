<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: hotels.php');
    exit;
}

$id = intval($_GET['id']);

$query = "SELECT * FROM hotels WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: hotels.php');
    exit;
}

$hotel = $result->fetch_assoc();
$hotelFeatures = explode(',', $hotel['features']);

$bookingStatsQuery = "
    SELECT 
        COUNT(*) as total_bookings,
        SUM(total_price) as total_revenue,
        COUNT(DISTINCT user_id) as unique_guests
    FROM bookings
    WHERE hotel_id = ?
";
$stmt = $conn->prepare($bookingStatsQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$bookingStats = $stmt->get_result()->fetch_assoc();

$recentBookingsQuery = "
    SELECT b.id, b.check_in, b.check_out, b.guests, b.total_price, b.status,
           u.fullname as guest_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.hotel_id = ?
    ORDER BY b.booking_date DESC
    LIMIT 5
";
$stmt = $conn->prepare($recentBookingsQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$recentBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$reviewsQuery = "
    SELECT r.id, r.rating, r.comment, r.review_date,
           u.fullname as reviewer_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.hotel_id = ?
    ORDER BY r.review_date DESC
    LIMIT 5
";
$stmt = $conn->prepare($reviewsQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['title']); ?> - Hotel Booking Admin</title>
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
                    <h1><?php echo htmlspecialchars($hotel['title']); ?></h1>
                    <div class="header-actions">
                        <a href="hotel_edit.php?id=<?php echo $id; ?>" class="secondary-btn">
                            <i class="fas fa-edit"></i> Edit Hotel
                        </a>
                        <a href="hotels.php" class="secondary-btn">
                            <i class="fas fa-arrow-left"></i> Back to Hotels
                        </a>
                    </div>
                </div>

                <div class="hotel-details-container">
                    <div class="hotel-details-header">
                        <div class="hotel-image">
                            <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['title']); ?>">
                        </div>
                        <div class="hotel-info">
                            <div class="hotel-location">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?>
                            </div>

                            <div class="hotel-price">
                                <i class="fas fa-dollar-sign"></i> <?php echo number_format($hotel['price'], 2); ?> TND /Night
                            </div>
                            <div class="hotel-region">
                                <i class="fas fa-globe"></i> Region: <?php echo ucfirst(htmlspecialchars($hotel['region'])); ?>
                            </div>

                            <div class="hotel-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $hotel['rating']): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i - 0.5 <= $hotel['rating']): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span><?php echo number_format($hotel['rating'], 1); ?></span>
                            </div>

                            <div class="hotel-features" style="display:flex; flex-direction:row; gap:10px;">
                                <h3>Features:</h3>
                                <ul>
                                    <?php foreach ($hotelFeatures as $feature): ?>
                                        <?php if (!empty($feature)): ?>
                                        <li>
                                            <?php 
                                            $icon = '';
                                            switch ($feature) {
                                                case 'piscine': 
                                                    $icon = 'fa-swimming-pool';
                                                    $featureName = 'Swimming Pool';
                                                    break;
                                                case 'plage': 
                                                    $icon = 'fa-umbrella-beach';
                                                    $featureName = 'Beach Access';
                                                    break;
                                                case 'restaurant': 
                                                    $icon = 'fa-utensils';
                                                    $featureName = 'Restaurant';
                                                    break;
                                                case 'spa': 
                                                    $icon = 'fa-spa';
                                                    $featureName = 'Spa';
                                                    break;
                                                case 'wifi': 
                                                    $icon = 'fa-wifi';
                                                    $featureName = 'WiFi';
                                                    break;
                                                default: 
                                                    $icon = 'fa-check';
                                                    $featureName = ucfirst($feature);
                                            }
                                            ?>
                                            <i class="fas <?php echo $icon; ?>"></i> <?php echo $featureName; ?>
                                        </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="hotel-stats-cards">
                        <div class="stat-card">
                            <div class="stat-card-content">
                                <h3>Total Bookings</h3>
                                <h2><?php echo number_format($bookingStats['total_bookings'] ?? 0); ?></h2>
                            </div>
                            <div class="stat-card-icon bookings">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-content">
                                <h3>Total Revenue</h3>
                                <h2>$<?php echo number_format($bookingStats['total_revenue'] ?? 0, 2); ?></h2>
                            </div>
                            <div class="stat-card-icon revenue">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-content">
                                <h3>Unique Guests</h3>
                                <h2><?php echo number_format($bookingStats['unique_guests'] ?? 0); ?></h2>
                            </div>
                            <div class="stat-card-icon users">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-content">
                                <h3>Reviews</h3>
                                <h2><?php echo number_format($hotel['reviews_count']); ?></h2>
                            </div>
                            <div class="stat-card-icon reviews">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>

                    <div class="hotel-sections">
                        <div class="section">
                            <div class="section-header">
                                <h3>Recent Bookings</h3>
                                <a href="bookings.php?hotel_id=<?php echo $id; ?>" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Guest</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th>Guests</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentBookings as $booking): ?>
                                        <tr>
                                            <td>#BK-<?php echo $booking['id']; ?></td>
                                            <td>
                                                <div class="user-info-cell">
                                                    <span><?php echo htmlspecialchars($booking['guest_name']); ?></span>
                                                </div>
                                            </td>
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
                                        <?php if (empty($recentBookings)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No bookings found for this hotel</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="section">
                            <div class="section-header">
                                <h3>Recent Reviews</h3>
                                <a href="reviews.php?hotel_id=<?php echo $id; ?>" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="reviews-container">
                                <?php foreach ($reviews as $review): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <img src="https://randomuser.me/api/portraits/women/33.jpg" alt="Reviewer">
                                            <div>
                                                <h4><?php echo htmlspecialchars($review['reviewer_name']); ?></h4>
                                                <span class="review-date"><?php echo date('M d, Y', strtotime($review['review_date'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="review-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review['rating']): ?>
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
                                            <button class="review-action"><i class="fas fa-reply"></i> Reply</button>
                                            <button class="review-action"><i class="fas fa-flag"></i> Report</button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($reviews)): ?>
                                <div class="no-data">No reviews found for this hotel</div>
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
