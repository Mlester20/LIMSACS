<?php
require_once __DIR__ . '/../../services/ParentGuardiansService.php';
require_once __DIR__ . '/../../../database/config/config.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'registrar') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Search query is required']);
    exit();
}

try {
    $keyword = trim($_GET['q']);
    $parentGuardianService = new ParentGuardianService($con);
    $results = $parentGuardianService->searchParentsGuardians($keyword);

    echo json_encode([
        'success' => true,
        'data'    => $results ?? [],
        'count'   => count($results ?? [])
    ]);

} catch (Exception $e) {
    error_log('Search guardians error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while searching']);
    exit();
}