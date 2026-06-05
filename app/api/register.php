<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST'
    ]);
    exit();
}

try {
    // Include database config
    require_once __DIR__ . '/../../database/config/config.php';

    // Include RegisterModel
    require_once __DIR__ . '/../models/RegisterModel.php';

    // Get JSON input
    $inputData = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!$inputData) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON input'
        ]);
        exit();
    }

    // Extract data
    $fullName = trim($inputData['full_name'] ?? '');
    $email = trim($inputData['email'] ?? '');
    $password = $inputData['password'] ?? '';
    $role = trim($inputData['role'] ?? 'user');

    // Create RegisterModel instance
    $registerModel = new RegisterModel($con);

    // Register user
    $result = $registerModel->register($fullName, $email, $password, $role);

    // Return response
    if ($result['success']) {
        http_response_code(201);
    } else {
        http_response_code(400);
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
