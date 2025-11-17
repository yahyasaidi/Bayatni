<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();

$errors = [];
$success = false;
$review = null;


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: reviews.php');
    exit;
}

$id = intval($_GET['id']);


$query = "SELECT r.*, u.fullname as reviewer_name, h.title as hotel_name
          FROM reviews r
          JOIN users u ON r.user_id = u.id
          JOIN hotels h ON r.hotel_id = h.id
          WHERE r.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: reviews.php');
    exit;
}

$review = $result->fetch_assoc();

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
    $status = $_POST['status'] ?? 'published';
    

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
        $query = "UPDATE reviews SET 
                  user_id = ?, 
                  hotel_id = ?, 
                  rating = ?, 
                  comment = ? 
                  WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiisi", $user_id, $hotel_id, $rating, $comment, $id);
        
        if ($stmt->execute()) {
            $success = true;
            
            $query = "SELECT r.*, u.fullname as reviewer_name, h.title as hotel_name
                      FROM reviews r
                      JOIN users u ON r.user_id = u.id
                      JOIN hotels h ON r.hotel_id = h.id
                      WHERE r.id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $review = $result->fetch_assoc();
        } else {
            $errors[] = "Error updating review: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review - Hotel Booking Admin</title>
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
                    <h1>Edit Review</h1>
                    <a href="reviews.php" class="secondary-btn">
                        <i class="fas fa-arrow-left"></i> Back to Reviews
                    </a>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    Review updated successfully!
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
                        <form action="review_edit.php?id=<?php echo $id; ?>" method="POST" class="form">
                            <div class="form-group">
                                <label for="user_id">User</label>
                                <select id="user_id" name="user_id" class="form-control" required>
                                    <option value="">Select User</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo $review['user_id'] == $user['id'] ? 'selected' : ''; ?>>
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
                                    <option value="<?php echo $hotel['id']; ?>" <?php echo $review['hotel_id'] == $hotel['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($hotel['title']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <div class="rating-input">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="rating-item">
                                        <input type="radio" id="rating-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $review['rating'] == $i ? 'checked' : ''; ?> required>
                                        <label for="rating-<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="comment">Comment</label>
                                <textarea id="comment" name="comment" rows="5" class="form-control" required><?php echo htmlspecialchars($review['comment']); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="published" selected>Published</option>
                                    <option value="pending">Pending</option>
                                    <option value="flagged">Flagged</option>
                                </select>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="primary-btn">
                                    <i class="fas fa-save"></i> Update Review
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
