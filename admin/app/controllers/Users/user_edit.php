<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();

$errors = [];
$success = false;
$user = null;


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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthday = $_POST['birthday'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($firstname)) {
        $errors[] = "First name is required";
    }
    
    if (empty($lastname)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {

        $checkQuery = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }
    

    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
    }
    
    if (empty($birthday)) {
        $errors[] = "Birthday is required";
    }
    

    if (empty($errors)) {
        $fullname = $firstname . ' ' . $lastname;
        
        if (!empty($password)) {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET 
                      fullname = ?, 
                      firstname = ?, 
                      lastname = ?, 
                      birthday = ?, 
                      email = ?, 
                      password = ?,
                      status = ?
                      WHERE id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssi", $fullname, $firstname, $lastname, $birthday, $email, $hashed_password, $status, $id);
        } else {

            $query = "UPDATE users SET 
                      fullname = ?, 
                      firstname = ?, 
                      lastname = ?, 
                      birthday = ?,
                      status = ?, 
                      email = ? 
                      WHERE id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssi", $firstname, $lastname, $fullname, $birthday, $status, $email, $id);
        }
        
        if ($stmt->execute()) {
            $success = true;

            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $errors[] = "Error updating user: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Hotel Booking Admin</title>
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
                    <h1>Edit User</h1>
                    <a href="users.php" class="secondary-btn">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    User updated successfully!
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
                        <form action="user_edit.php?id=<?php echo $id; ?>" method="POST" class="form">
                        <div class="form-rows">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="firstname">First Name</label>
                                    <input type="text" id="firstname" name="firstname" class="form-control" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="lastname">Last Name</label>
                                    <input type="text" id="lastname" name="lastname" class="form-control" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="password">New Password (leave blank to keep current)</label>
                                    <input type="password" id="password" name="password" class="form-control">
                                    <small class="form-text text-muted">Password must be at least 6 characters</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="birthday">Birthday</label>
                                    <input type="date" id="birthday" name="birthday" style="padding : 8px 15px; border: 1px gray; border-radius:5px;" class="form-control" value="<?php echo htmlspecialchars($user['birthday']); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="active" selected>Active</option>
                                    <option value="suspended">suspended</option>
                                </select>
                            </div>
                        </div>
                            <br>
                            <div class="form-actions" style="display:flex; flex-direction:row; gap:10px;">
                                <button type="submit" class="primary-btn">
                                    <i class="fas fa-save"></i> Update User
                                </button>
                                <a href="users.php" class="secondary-btn">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
