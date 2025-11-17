<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();

$errors = [];
$success = false;
$usersQuery = "SELECT id, fullname FROM users ORDER BY fullname";
$usersResult = $conn->query($usersQuery);
$users = [];

if ($usersResult) {
    while ($row = $usersResult->fetch_assoc()) {
        $users[] = $row;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id = intval($_POST['user_id'] ?? 0);
    $hotel_id = intval($_POST['hotel_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    

    if ($user_id <= 0) {
        $errors[] = "Please select a user";
    }
    
    if ($hotel_id <= 0) {
        $errors[] = "Please select a hotel";
    }
    
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5";
    }
    
    if (empty($comment)) {
        $errors[] = "Comment is required";
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO reviews (user_id, hotel_id, rating, comment, review_date) 
                  VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiis", $user_id, $hotel_id, $rating, $comment);
        
        if ($stmt->execute()) {

            $updateHotelQuery = "UPDATE hotels SET reviews_count = reviews_count + 1 WHERE id = ?";
            $stmt = $conn->prepare($updateHotelQuery);
            $stmt->bind_param("i", $hotel_id);
            $stmt->execute();
            
            $success = true;
        } else {
            $errors[] = "Error adding review: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Review - Hotel Booking Admin</title>
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
                    <h1>Add New Review</h1>
                    <a href="reviews.php" class="secondary-btn">
                        <i class="fas fa-arrow-left"></i> Back to Reviews
                    </a>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    Review added successfully! <a href="reviews.php">Return to review list</a>
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
                        <form action="review_add.php" method="POST" class="form">
                            <div class="form-group">
                                <label for="user_id">User</label>
                                <select id="user_id" name="user_id" class="form-control" required>
                                    <option value="">Select User</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['fullname']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="hotel_id">Hotel</label>
                                <select id="hotel_id" name="hotel_id" class="form-control" required>
                                    <option value="">Select Hotel</option>
                                    <?php foreach ($hotels as $hotel): ?>
                                    <option value="<?php echo $hotel['id']; ?>" <?php echo (isset($_POST['hotel_id']) && $_POST['hotel_id'] == $hotel['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($hotel['title']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <div class="rating-input" style="display:flex; flex-direction:row; gap:10px;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="rating-item">
                                        <input type="radio" id="rating-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo (isset($_POST['rating']) && $_POST['rating'] == $i) ? 'checked' : ''; ?> required>
                                        <label for="rating-<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="comment">Comment</label>
                                <textarea id="comment" name="comment" rows="5" class="form-control" required><?php echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>
                            </div>
                            <br>
                            <div class="form-actions" style="display:flex; flex-direction:row; gap:10px;">
                                <button type="submit" class="primary-btn">
                                    <i class="fas fa-save"></i> Save Review
                                </button>
                                <a href="reviews.php" class="secondary-btn">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
