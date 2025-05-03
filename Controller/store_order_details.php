<?php
session_start();

header('Content-Type: application/json');

try {
    $input = file_get_contents('php://input');
    $orderDetails = json_decode($input, true);

    if (!$orderDetails) {
        throw new Exception('Invalid order details');
    }

    $_SESSION['order_details'] = $orderDetails;

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>