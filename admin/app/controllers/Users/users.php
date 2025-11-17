    <?php
    require_once __DIR__ .'/../../../config/config.php';
    requireLogin();


    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        

        $checkBookingsQuery = "SELECT COUNT(*) as count FROM bookings WHERE user_id = ?";
        $stmt = $conn->prepare($checkBookingsQuery);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            $deleteError = "Cannot delete user with active bookings.";
        } else {

            $deleteQuery = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $deleteSuccess = "User deleted successfully.";
            } else {
                $deleteError = "Error deleting user: " . $conn->error;
            }
        }
    }
    
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    $query = "SELECT * FROM users WHERE 1=1";

    if (!empty($search)) {
        $searchTerm = "%{$search}%";
        $query .= " AND (id = ? OR fullname LIKE ?)";
    }

    $dateJoined = isset($_GET['date_joined']) ? $_GET['date_joined'] : '';
    if (!empty($dateJoined)) {
        switch ($dateJoined) {
            case 'today':
                $query .= " AND DATE(created_at) = CURDATE()";
                break;
            case 'week':
                $query .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $query .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $query .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
        }
    }

    $query .= " ORDER BY id DESC";

    $stmt = $conn->prepare($query);

    if (!empty($search)) {
        $searchId = is_numeric($search) ? intval($search) : 0;
        $stmt->bind_param("is", $searchId, $searchTerm);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    $totalUsersQuery = "SELECT COUNT(*) as total FROM users";
    $result = $conn->query($totalUsersQuery);
    $totalUsers = $result->fetch_assoc()['total'];
    ?>
<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Hotel Booking Admin</title>
    <link rel="stylesheet" href="../../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
    <?php include __DIR__ .'/../../../includes/sidebar.php'; ?>

        <main class="main-content">
        <?php include __DIR__ .'/../../../includes/header.php'; ?>

            <div class="dashboard-content" id="users">
                <div class="page-header">
                    <h1>Users Management</h1>
                    <a href="user_add.php" class="primary-btn">
                        <i class="fas fa-plus"></i> Add New User
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


                <div class="user-stats">
                    <div class="user-stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Total Users</h3>
                            <h2><?php echo number_format($totalUsers); ?></h2>
                        </div>
                    </div>
                </div>


                <form action="users.php" method="GET" class="search-section">
                    <div class="search-group">
                        <label for="search-input">Search by Name or User ID</label>
                        <input 
                            type="text" 
                            id="search-input" 
                            name="search" 
                            class="search-bar"
                            placeholder="Enter name or user ID"
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        >
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    

                </form>

                <div class="table-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $index => $user): ?>
                                <?php 
                                ?>
                                <tr>
                                    <td>#User-<?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="user-info-cell">
                                            <span><?php echo htmlspecialchars($user['fullname']); ?></span>
                                        </div>
                                    </td>
                                    
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    
                                    <td><span class="status-badge"> <?php echo $user['status']; ?> </span></td>
                                    
                                    <td>
                                        <div class="action-buttons">
                                            <a href="user_view.php?id=<?php echo $user['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                                            <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                            <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="10" class="text-center">No users found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
        </main>
    </div>

    <script src="../js/users.js"></script>
</body>
</html>
