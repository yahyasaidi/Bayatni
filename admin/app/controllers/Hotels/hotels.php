<?php
require_once __DIR__ .'/../../../config/config.php';

requireLogin();

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $checkBookingsQuery = "SELECT COUNT(*) as count FROM bookings WHERE hotel_id = ?";
    $stmt = $conn->prepare($checkBookingsQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $deleteError = "Cannot delete hotel with active bookings.";
    } else {
        $deleteQuery = "DELETE FROM hotels WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $deleteSuccess = "Hotel deleted successfully.";
        } else {
            $deleteError = "Error deleting hotel: " . $conn->error;
        }
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name-asc';

$query = "SELECT * FROM hotels WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (title LIKE '%" . $conn->real_escape_string($search) . "%' OR 
                     region LIKE '%" . $conn->real_escape_string($search) . "%' OR
                     location LIKE '%" . $conn->real_escape_string($search) . "%')";
}

if (!empty($location)) {
    $query .= " AND location LIKE '%" . $conn->real_escape_string($location) . "%'";
}

if (!empty($rating)) {
    $query .= " AND rating >= " . intval($rating);
}

switch ($sortBy) {
    case 'name-asc':
        $query .= " ORDER BY title ASC";
        break;
    case 'name-desc':
        $query .= " ORDER BY title DESC";
        break;
    case 'rating-desc':
        $query .= " ORDER BY rating DESC";
        break;
    case 'rating-asc':
        $query .= " ORDER BY rating ASC";
        break;
    case 'price-desc':
        $query .= " ORDER BY price DESC";
        break;
    case 'price-asc':
        $query .= " ORDER BY price ASC";
        break;
    default:
        $query .= " ORDER BY title ASC";
}

$result = $conn->query($query);
$hotels = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $hotels[] = $row;
    }
}


$locationsQuery = "SELECT DISTINCT location FROM hotels ORDER BY location";
$locationsResult = $conn->query($locationsQuery);
$locations = [];

if ($locationsResult) {
    while ($row = $locationsResult->fetch_assoc()) {
        $locations[] = $row['location'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotels Management - Hotel Booking Admin</title>
    <link rel="stylesheet" href="../../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        .search-container {
            margin-bottom: 20px;
            width: 100%;
        }
        .search-input {
            display: flex;
            position: relative;
        }
        .search-input input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .search-input button {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            background: transparent;
            border: none;
            padding: 0 15px;
            cursor: pointer;
            color: #666;
        }
        .search-input button:hover {
            color: #333;
        }
        

        @media (max-width: 768px) {
            .filter-section {
                flex-direction: column;
            }
            .search-container {
                order: -1;
            }
            .filter-group {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include __DIR__ .'/../../../includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include __DIR__ .'/../../../includes/header.php'; ?>

            <div class="dashboard-content" id="hotels">
                <div class="page-header">
                    <h1>Hotels Management</h1>
                    <a href="hotel_add.php" class="primary-btn">
                        <i class="fas fa-plus"></i> Add New Hotel
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

                <form action="hotels.php" method="GET" class="filter-section">
                    <div class="search-container">
                        <div class="search-input">
                            <input type="text" name="search" placeholder="Search hotels by name, description or location..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="location-filter">Location</label>
                        <select id="location-filter" name="location">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo $location == $loc ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="status-filter">Status</label>
                        <select id="status-filter" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="maintenance" <?php echo $status == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="rating-filter">Rating</label>
                        <select id="rating-filter" name="rating">
                            <option value="">All Ratings</option>
                            <option value="5" <?php echo $rating == 5 ? 'selected' : ''; ?>>5 Stars</option>
                            <option value="4" <?php echo $rating == 4 ? 'selected' : ''; ?>>4 Stars & Up</option>
                            <option value="3" <?php echo $rating == 3 ? 'selected' : ''; ?>>3 Stars & Up</option>
                            <option value="2" <?php echo $rating == 2 ? 'selected' : ''; ?>>2 Stars & Up</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="sort-by">Sort By</label>
                        <select id="sort-by" name="sort_by">
                            <option value="name-asc" <?php echo $sortBy == 'name-asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name-desc" <?php echo $sortBy == 'name-desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                            <option value="rating-desc" <?php echo $sortBy == 'rating-desc' ? 'selected' : ''; ?>>Rating (High to Low)</option>
                            <option value="rating-asc" <?php echo $sortBy == 'rating-asc' ? 'selected' : ''; ?>>Rating (Low to High)</option>
                            <option value="price-desc" <?php echo $sortBy == 'price-desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                            <option value="price-asc" <?php echo $sortBy == 'price-asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        </select>
                    </div>
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </form>

                <div class="hotels-grid">
                    <?php foreach ($hotels as $hotel): ?>
                    <div class="hotel-item">
                        <div class="hotel-image">
                            <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['title']); ?>">
                            <div class="hotel-status active">Active</div>
                        </div>
                        <div class="hotel-details">
                            <h3><?php echo htmlspecialchars($hotel['title']); ?></h3>
                            <p class="hotel-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?></p>
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
                            <div class="hotel-info">
                                <div class="info-item">
                                    <i class="fas fa-bed"></i>
                                    <span>Multiple Rooms</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span><?php echo number_format($hotel['price'], 2); ?> TND /Night</span>
                                </div>
                            </div>
                            <div class="hotel-actions">
                                <a href="hotel_view.php?id=<?php echo $hotel['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i> View</a>
                                <a href="hotel_edit.php?id=<?php echo $hotel['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</a>
                                <a href="hotels.php?action=delete&id=<?php echo $hotel['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this hotel?');"><i class="fas fa-trash"></i> Delete</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($hotels)): ?>
                    <div class="no-data">No hotels found matching your criteria.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'filter-btn clear-btn';
        clearButton.innerHTML = '<i class="fas fa-times"></i> Clear Filters';
        clearButton.style.marginLeft = '10px';
        clearButton.style.background = '#f0f0f0';
        clearButton.style.color = '#333';
        
        clearButton.addEventListener('click', function() {
            window.location.href = 'hotels.php';
        });
        
        const filterBtn = document.querySelector('.filter-btn');
        filterBtn.parentNode.insertBefore(clearButton, filterBtn.nextSibling);
        

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.querySelector('input[name="search"]').focus();
            }
        });
    });
    </script>
    <script src="/development/admin/public/js/hotels.js"></script>
</body>
</html>