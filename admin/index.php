<?php
require_once __DIR__ .'/config/config.php';
requireLogin();

$stats = [
    'total_bookings' => 0,
    'revenue' => 0,
    'new_users' => 0,
    'occupancy_rate' => 0
];

$bookingQuery = "SELECT COUNT(*) as total FROM bookings";
$result = $conn->query($bookingQuery);
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_bookings'] = $row['total'];
}

$revenueQuery = "SELECT SUM(total_price) as total FROM bookings";
$result = $conn->query($revenueQuery);
if ($result && $row = $result->fetch_assoc()) {
    $stats['revenue'] = $row['total'] ?? 0;
}

$usersQuery = "SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$result = $conn->query($usersQuery);
if ($result && $row = $result->fetch_assoc()) {
    $stats['new_users'] = $row['total'];
}

$stats['occupancy_rate'] = 78;

$recentBookingsQuery = "
    SELECT b.id, b.check_in, b.check_out, b.total_price, b.status, 
           u.fullname as guest_name, h.title as hotel_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN hotels h ON b.hotel_id = h.id
    ORDER BY b.booking_date DESC
    LIMIT 5
";
$recentBookings = [];
$result = $conn->query($recentBookingsQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentBookings[] = $row;
    }
}

$popularHotelsQuery = "
    SELECT h.id, h.title, h.location, h.price, h.rating, h.image_url,
           COUNT(b.id) as booking_count
    FROM hotels h
    LEFT JOIN bookings b ON h.id = b.hotel_id
    GROUP BY h.id
    ORDER BY booking_count DESC
    LIMIT 4
";
$popularHotels = [];
$result = $conn->query($popularHotelsQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $popularHotels[] = $row;
    }
}


$recentReviewsQuery = "
    SELECT r.id, r.rating, r.comment, r.review_date,
           u.fullname as reviewer_name, h.title as hotel_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN hotels h ON r.hotel_id = h.id
    ORDER BY r.review_date DESC
    LIMIT 3
";
$recentReviews = [];
$result = $conn->query($recentReviewsQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentReviews[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking Admin Dashboard</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">

        <?php include __DIR__ .'/includes/sidebar.php'; ?>

        <main class="main-content"> 

            <?php include __DIR__ .'/includes/header.php'; ?>

            <div class="dashboard-content" id="dashboard">
                <div class="page-header">
                    <h1>Dashboard</h1>
                </div>

                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <h3>Total Bookings</h3>
                            <h2><?php echo number_format($stats['total_bookings']); ?></h2>
                        </div>
                        <div class="stat-card-icon bookings">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <h3>Revenue</h3>
                            <h2><?php echo number_format($stats['revenue']); ?> TND</h2>
                        </div>
                        <div class="stat-card-icon revenue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <h3>New Users</h3>
                            <h2><?php echo number_format($stats['new_users']); ?></h2>  
                        </div>
                        <div class="stat-card-icon users">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                </div>


                <div class="table-card">
                    <div class="table-header">
                        <h3>Recent Bookings</h3>
                        <a href="/development/admin/app/controllers/bookings/bookings.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Guest</th>
                                    <th>Hotel</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
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
                                    <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                                    <td><span class="status-badge <?php echo strtolower($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                    <td><?php echo number_format($booking['total_price'], 2); ?> TND</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="/development/admin/app/controllers/bookings/booking_view.php?id=<?php echo $booking['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                                            <a href="/development/admin/app/controllers//bookings/booking_edit.php?id=<?php echo $booking['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                            <a href="/development/admin/app/controllers/bookings/bookings.php?action=delete&id=<?php echo $booking['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this booking?');"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentBookings)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No recent bookings found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="recent-reviews">
                    <div class="section-header">
                        <h3>Recent Reviews</h3>
                        <a href="/development/admin/app/controllers/reviews/reviews.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="reviews-container">
                        <?php foreach ($recentReviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div>
                                        <h4><?php echo htmlspecialchars($review['reviewer_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($review['hotel_name']); ?></p>
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
                                <span class="review-date"><?php echo date('M d, Y', strtotime($review['review_date'])); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($recentReviews)): ?>
                        <div class="no-data">No reviews found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
