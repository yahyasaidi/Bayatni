<?php
require_once __DIR__ .'/../../../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';
$bookingIds = json_decode($_POST['booking_ids'] ?? '[]', true);

if (empty($action) || empty($bookingIds)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$response = ['success' => false, 'message' => 'Unknown error'];

switch ($action) {
    case 'confirm':
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id IN (" . implode(',', array_fill(0, count($bookingIds), '?')) . ")");
        break;
    case 'cancel':
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id IN (" . implode(',', array_fill(0, count($bookingIds), '?')) . ")");
        break;
    case 'delete':
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id IN (" . implode(',', array_fill(0, count($bookingIds), '?')) . ")");
        break;
    case 'export':
        // This would typically generate a CSV or Excel file
        echo json_encode(['success' => true, 'message' => 'Export functionality would be implemented here']);
        exit;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

if ($stmt) {
    $types = str_repeat('i', count($bookingIds));
    $stmt->bind_param($types, ...$bookingIds);
    
    if ($stmt->execute()) {
        $affectedRows = $stmt->affected_rows;
        $response = [
            'success' => true,
            'message' => ucfirst($action) . " action completed successfully on $affectedRows bookings"
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $conn->error
        ];
    }
}

echo json_encode($response);
