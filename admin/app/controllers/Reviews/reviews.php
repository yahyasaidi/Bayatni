<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    

    $deleteQuery = "DELETE FROM reviews WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $deleteSuccess = "Review deleted successfully.";
    } else {
        $deleteError = "Error deleting review: " . $conn->error;
    }
}


$hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : 'month';


$query = "SELECT r.id, r.rating, r.comment, r.review_date,
                 u.fullname as reviewer_name, h.title as hotel_name,
                 u.id as user_id, h.id as hotel_id
          FROM reviews r
          JOIN users u ON r.user_id = u.id
          JOIN hotels h ON r.hotel_id = h.id
          WHERE 1=1";

if ($hotel_id > 0) {
    $query .= " AND r.hotel_id = " . $hotel_id;
}

if ($user_id > 0) {
    $query .= " AND r.user_id = " . $user_id;
}

if ($rating > 0) {
    $query .= " AND r.rating = " . $rating;
}


switch ($dateRange) {
    case 'week':
        $query .= " AND r.review_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $query .= " AND r.review_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        break;
    case 'quarter':
        $query .= " AND r.review_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
        break;
    case 'year':
        $query .= " AND r.review_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        break;
}

$query .= " ORDER BY r.review_date DESC";

$result = $conn->query($query);
$reviews = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

$hotelsQuery = "SELECT id, title FROM hotels ORDER BY title";
$hotelsResult = $conn->query($hotelsQuery);
$hotels = [];

if ($hotelsResult) {
    while ($row = $hotelsResult->fetch_assoc()) {
        $hotels[] = $row;
    }
}

$totalReviewsQuery = "SELECT COUNT(*) as total, AVG(rating) as avg_rating FROM reviews";
$result = $conn->query($totalReviewsQuery);
$reviewStats = $result->fetch_assoc();

$positiveReviewsQuery = "SELECT COUNT(*) as total FROM reviews WHERE rating >= 4";
$result = $conn->query($positiveReviewsQuery);
$positiveReviews = $result->fetch_assoc()['total'];
$positivePercentage = ($reviewStats['total'] > 0) ? round(($positiveReviews / $reviewStats['total']) * 100) : 0;


$neutralReviewsQuery = "SELECT COUNT(*) as total FROM reviews WHERE rating like 3";
$result = $conn->query($neutralReviewsQuery);
$neutralReviews = $result->fetch_assoc()['total'];
$neutralPercentage = ($reviewStats['total'] > 0) ? round(($neutralReviews / $reviewStats['total']) * 100) : 0;



$negativeReviewsQuery = "SELECT COUNT(*) as total FROM reviews WHERE rating < 3";
$result = $conn->query($negativeReviewsQuery);
$negativeReviews = $result->fetch_assoc()['total'];
$negativePercentage = ($reviewStats['total'] > 0) ? round(($negativeReviews / $reviewStats['total']) * 100) : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews Management - Hotel Booking Admin</title>
    <link rel="stylesheet" href="../../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
    <?php include __DIR__ .'/../../../includes/sidebar.php'; ?>

        <main class="main-content">
        <?php include __DIR__ .'/../../../includes/header.php'; ?>

            <div class="dashboard-content" id="reviews">
                <div class="page-header">
                    <h1>Reviews Management</h1>
                    <a href="review_add.php" class="primary-btn">
                        <i class="fas fa-plus"></i> Add New Review
                    </a>
                </div>

                <?php if (isset($deleteSuccess)): ?>
                <div class="alert alert-success">
                    <?php echo $deleteSuccess; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($deleteError)): ?>
                <div class="alert alert-danger">
                    <?php echo $deleteError; ?>
                </div>
                <?php endif; ?>

                <div class="review-stats">
                    <div class="review-stat">
                        <span class="stat-value"><?php echo number_format($reviewStats['avg_rating'], 1); ?></span>
                        <span class="stat-label">Average Rating</span>
                    </div>
                    <div class="review-stat">
                        <span class="stat-value"><?php echo number_format($reviewStats['total']); ?></span>
                        <span class="stat-label">Total Reviews</span>
                    </div>
                    <div class="review-stat">
                        <span class="stat-value"><?php echo $positivePercentage; ?>%</span>
                        <span class="stat-label">Positive Reviews</span>
                    </div>
                    <div class="review-stat">
                        <span class="stat-value"><?php echo $neutralPercentage; ?>%</span>
                        <span class="stat-label">Neutral Reviews</span>
                    </div>
                    <div class="review-stat">
                        <span class="stat-value"><?php echo $negativePercentage; ?>%</span>
                        <span class="stat-label">Negative Reviews</span>
                    </div>
                </div>

                <br>
                <form action="reviews.php" method="GET" class="filter-section">
                    <div class="filter-group">
                        <label for="hotel-filter">Hotel</label>
                        <select id="hotel-filter" name="hotel_id">
                            <option value="">All Hotels</option>
                            <?php foreach ($hotels as $hotel): ?>
                            <option value="<?php echo $hotel['id']; ?>" <?php echo $hotel_id == $hotel['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($hotel['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="rating-filter">Rating</label>
                        <select id="rating-filter" name="rating">
                            <option value="">All Ratings</option>
                            <option value="5" <?php echo $rating == 5 ? 'selected' : ''; ?>>5 Stars</option>
                            <option value="4" <?php echo $rating == 4 ? 'selected' : ''; ?>>4 Stars</option>
                            <option value="3" <?php echo $rating == 3 ? 'selected' : ''; ?>>3 Stars</option>
                            <option value="2" <?php echo $rating == 2 ? 'selected' : ''; ?>>2 Stars</option>
                            <option value="1" <?php echo $rating == 1 ? 'selected' : ''; ?>>1 Star</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="status-filter">Status</label>
                        <select id="status-filter" name="status">
                            <option value="">All Status</option>
                            <option value="published" <?php echo $status == 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="flagged" <?php echo $status == 'flagged' ? 'selected' : ''; ?>>Flagged</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="date-range">Date Range</label>
                        <select id="date-range" name="date_range">
                            <option value="week" <?php echo $dateRange == 'week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="month" <?php echo $dateRange == 'month' ? 'selected' : ''; ?>>This Month</option>
                            <option value="quarter" <?php echo $dateRange == 'quarter' ? 'selected' : ''; ?>>This Quarter</option>
                            <option value="year" <?php echo $dateRange == 'year' ? 'selected' : ''; ?>>This Year</option>
                            <option value="all" <?php echo $dateRange == 'all' ? 'selected' : ''; ?>>All Time</option>
                        </select>
                    </div>
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </form>


                <div class="reviews-list">
                    <?php foreach ($reviews as $index => $review): ?>
                    <?php 

                        $statuses = ['published', 'pending', 'flagged'];
                        $status = $statuses[$index % count($statuses)];
                    ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div>
                                    <h4><?php echo htmlspecialchars($review['reviewer_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($review['hotel_name']); ?></p>
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
                                <a href="reviews.php?action=delete&id=<?php echo $review['id']; ?>" class="review-action" onclick="return confirm('Are you sure you want to delete this review?');"><i class="fas fa-trash"></i> Delete</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($reviews)): ?>
                    <div class="no-data">No reviews found matching your criteria.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="Assets/js/reviews.js"></script>
</body>
</html>
