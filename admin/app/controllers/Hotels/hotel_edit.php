<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();

$errors = [];
$success = false;
$hotel = null;

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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $region = trim($_POST['region'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $features = isset($_POST['features']) ? implode(',', $_POST['features']) : '';
    

    if (empty($title)) {
        $errors[] = "Hotel title is required";
    }
    
    if (empty($location)) {
        $errors[] = "Location is required";
    }
    
    if ($price <= 0) {
        $errors[] = "Price must be greater than zero";
    }
    
    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5";
    }
    
    if (empty($region)) {
        $errors[] = "Region is required";
    }
    
    if (empty($image_url)) {
        $errors[] = "Image URL is required";
    }
    

    if (empty($errors)) {
        $query = "UPDATE hotels SET 
                  title = ?, 
                  location = ?, 
                  price = ?, 
                  rating = ?, 
                  region = ?, 
                  image_url = ?, 
                  features = ? 
                  WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdssssi", $title, $location, $price, $rating, $region, $image_url, $features, $id);
        
        if ($stmt->execute()) {
            $success = true;

            $query = "SELECT * FROM hotels WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $hotel = $result->fetch_assoc();
            $hotelFeatures = explode(',', $hotel['features']);
        } else {
            $errors[] = "Error updating hotel: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hotel - Hotel Booking Admin</title>
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
                    <h1>Edit Hotel</h1>
                    <a href="hotels.php" class="secondary-btn">
                        <i class="fas fa-arrow-left"></i> Back to Hotels
                    </a>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    Hotel updated successfully!
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
                        <form action="hotel_edit.php?id=<?php echo $id; ?>" method="POST" class="form">
                            <div class="form-group">
                                <label for="title">Hotel Name</label>
                                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($hotel['title']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="region">Region</label>
                                <select id="region" name="region" class="form-control" required>
                                    <option value="">Select Region</option>
                                    <option value="tunis" <?php echo $hotel['region'] == 'tunis' ? 'selected' : ''; ?>>Tunis</option>
                                    <option value="hammamet" <?php echo $hotel['region'] == 'hammamet' ? 'selected' : ''; ?>>Hammamet</option>
                                    <option value="sousse" <?php echo $hotel['region'] == 'sousse' ? 'selected' : ''; ?>>Sousse</option>
                                    <option value="djerba" <?php echo $hotel['region'] == 'djerba' ? 'selected' : ''; ?>>Djerba</option>
                                    <option value="tabarka" <?php echo $hotel['region'] == 'tabarka' ? 'selected' : ''; ?>>Tabarka</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($hotel['location']); ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="price">Price per Night</label>
                                    <div class="input-group">
                                        <span class="input-group-text">TND</span>
                                        <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($hotel['price']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="rating">Rating</label>
                                    <select id="rating" name="rating" class="form-control" required>
                                        <option value="">Select Rating</option>
                                        <option value="1" <?php echo $hotel['rating'] == 1 ? 'selected' : ''; ?>>1 Star</option>
                                        <option value="2" <?php echo $hotel['rating'] == 2 ? 'selected' : ''; ?>>2 Stars</option>
                                        <option value="3" <?php echo $hotel['rating'] == 3 ? 'selected' : ''; ?>>3 Stars</option>
                                        <option value="4" <?php echo $hotel['rating'] == 4 ? 'selected' : ''; ?>>4 Stars</option>
                                        <option value="5" <?php echo $hotel['rating'] == 5 ? 'selected' : ''; ?>>5 Stars</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="image_url">Image URL</label>
                                <input type="url" id="image_url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($hotel['image_url']); ?>" required>
                                <small class="form-text text-muted">Enter a URL for the hotel image</small>
                            </div>

                            <div class="form-group">
                                <label>Features</label>
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="feature-pool" name="features[]" value="piscine" <?php echo in_array('piscine', $hotelFeatures) ? 'checked' : ''; ?>>
                                        <label for="feature-pool">Swimming Pool</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="feature-beach" name="features[]" value="plage" <?php echo in_array('plage', $hotelFeatures) ? 'checked' : ''; ?>>
                                        <label for="feature-beach">Beach Access</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="feature-restaurant" name="features[]" value="restaurant" <?php echo in_array('restaurant', $hotelFeatures) ? 'checked' : ''; ?>>
                                        <label for="feature-restaurant">Restaurant</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="feature-spa" name="features[]" value="spa" <?php echo in_array('spa', $hotelFeatures) ? 'checked' : ''; ?>>
                                        <label for="feature-spa">Spa</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="feature-wifi" name="features[]" value="wifi" <?php echo in_array('wifi', $hotelFeatures) ? 'checked' : ''; ?>>
                                        <label for="feature-wifi">WiFi</label>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="form-actions" style="display:flex; flex-direction:row; gap:10px;">
                                <button type="submit" class="primary-btn">
                                    <i class="fas fa-save"></i> Update Hotel
                                </button>
                                <a href="hotels.php" class="secondary-btn">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
