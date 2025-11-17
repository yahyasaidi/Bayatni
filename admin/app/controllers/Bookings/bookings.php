<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $deleteQuery = "DELETE FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $deleteSuccess = "Booking deleted successfully.";
    } else {
        $deleteError = "Error deleting booking: " . $conn->error;
    }
}

$hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '';

$query = "SELECT b.id, b.check_in, b.check_out, b.guests, b.total_price, b.status, b.booking_date,
                 u.fullname as guest_name, h.title as hotel_name,
                 u.id as user_id, h.id as hotel_id
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN hotels h ON b.hotel_id = h.id
          WHERE 1=1";

if ($hotel_id > 0) {
    $query .= " AND b.hotel_id = " . $hotel_id;
}

if ($user_id > 0) {
    $query .= " AND b.user_id = " . $user_id;
}

if (!empty($status)) {
    $query .= " AND b.status = '" . $conn->real_escape_string($status) . "'";
}

if (!empty($dateRange)) {
    switch ($dateRange) {
        case 'today':
            $query .= " AND DATE(b.booking_date) = CURDATE()";
            break;
        case 'yesterday':
            $query .= " AND DATE(b.booking_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'week':
            $query .= " AND b.booking_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $query .= " AND b.booking_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            break;
        case 'upcoming':
            $query .= " AND b.check_in >= CURDATE()";
            break;
        case 'past':
            $query .= " AND b.check_out < CURDATE()";
            break;
    }
}

$query .= " ORDER BY b.booking_date DESC";


$result = $conn->query($query);
$bookings = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
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

$totalBookingsQuery = "SELECT COUNT(*) as total FROM bookings";
$result = $conn->query($totalBookingsQuery);
$totalBookings = $result->fetch_assoc()['total'];

$pendingBookingsQuery = "SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'";
$result = $conn->query($pendingBookingsQuery);
$pendingBookings = $result->fetch_assoc()['total'];

$confirmedBookingsQuery = "SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'";
$result = $conn->query($confirmedBookingsQuery);
$confirmedBookings = $result->fetch_assoc()['total'];

$cancelledBookingsQuery = "SELECT COUNT(*) as total FROM bookings WHERE status = 'cancelled'";
$result = $conn->query($cancelledBookingsQuery);
$cancelledBookings = $result->fetch_assoc()['total'];

$totalRevenueQuery = "SELECT SUM(total_price) as total FROM bookings";
$result = $conn->query($totalRevenueQuery);
$totalRevenue = $result->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - Hotel Booking Admin</title>
    <link rel="stylesheet" href="../../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
    <?php include __DIR__ .'/../../../includes/sidebar.php'; ?>

        <main class="main-content">
        <?php include __DIR__ .'/../../../includes/header.php'; ?>

            <div class="dashboard-content" id="bookings">
                <div class="page-header">
                    <h1>Bookings Management</h1>
                    <a href="booking_add.php" class="primary-btn">
                        <i class="fas fa-plus"></i> Add New Booking
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

                <div class="booking-stats">
                    <div class="booking-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-details">
                            <h3 style="width:max-content;">Total Bookings</h3>
                            <h2><?php echo number_format($totalBookings); ?></h2>
                        </div>
                    </div>
                    <div class="booking-stat-card">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Pending</h3>
                            <h2><?php echo number_format($pendingBookings); ?></h2>
                        </div>
                    </div>
                    <div class="booking-stat-card">
                        <div class="stat-icon confirmed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Confirmed</h3>
                            <h2><?php echo number_format($confirmedBookings); ?></h2>
                        </div>
                    </div>
                    <div class="booking-stat-card">
                        <div class="stat-icon cancelled">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Cancelled</h3>
                            <h2><?php echo number_format($cancelledBookings); ?></h2>
                        </div>
                    </div>
                    <div class="booking-stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Total Revenue</h3>
                            <h2><?php echo number_format($totalRevenue, 0); ?>TND</h2>
                        </div>
                    </div>
                </div>


                <form action="bookings.php" method="GET" class="filter-section">
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
                        <label for="status-filter">Status</label>
                        <select id="status-filter" name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="date-range">Date Range</label>
                        <select id="date-range" name="date_range">
                            <option value="">All Time</option>
                            <option value="today" <?php echo $dateRange == 'today' ? 'selected' : ''; ?>>Today</option>
                            <option value="yesterday" <?php echo $dateRange == 'yesterday' ? 'selected' : ''; ?>>Yesterday</option>
                            <option value="week" <?php echo $dateRange == 'week' ? 'selected' : ''; ?>>This Week</option>
                            <option value="month" <?php echo $dateRange == 'month' ? 'selected' : ''; ?>>This Month</option>
                            <option value="upcoming" <?php echo $dateRange == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="past" <?php echo $dateRange == 'past' ? 'selected' : ''; ?>>Past</option>
                        </select>
                    </div>
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </form>

                <div class="table-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th>Booking ID</th>
                                    <th>Guest</th>
                                    <th>Hotel</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Guests</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Booking Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><input type="checkbox" class="booking-select"></td>
                                    <td>#BK-<?php echo $booking['id']; ?></td>
                                    <td>
                                        <div class="user-info-cell">
                                            <span><?php echo htmlspecialchars($booking['guest_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                                    <td><?php echo $booking['guests']; ?></td>
                                    <td><span class="status-badge <?php echo strtolower($booking['status']); ?>"><?php echo ucfirst($booking['status']); ?></span></td>
                                    <td style="width:max-content;"><?php echo number_format($booking['total_price'], 2); ?>TND</td>
                                    <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="booking_view.php?id=<?php echo $booking['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                                            <a href="booking_edit.php?id=<?php echo $booking['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                            <a href="bookings.php?action=delete&id=<?php echo $booking['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this booking?');"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="11" class="text-center">No bookings found matching your criteria.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="bulk-actions">
                            <select id="bulk-action">
                                <option value="">Bulk Actions</option>
                                <option value="confirm">Confirm Selected</option>
                                <option value="cancel">Cancel Selected</option>
                                <option value="delete">Delete Selected</option>
                                <option value="export">Export Selected</option>
                            </select>
                        <button class="apply-btn">Apply</button>
                </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/development/admin/public/js/bookings.js"></script>
</body>
</html>
