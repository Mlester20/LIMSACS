<?php
session_start();

require_once __DIR__ . '/../../database/config/config.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../core/errorHandler.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../models/NotificationsModel.php';

    class NotificationsController{
        private $model;

        public function __construct($con){
            $this->model = new NotificationsModel($con);
        }

        public function markAllAsRead($userId){
            return $this->model->markAllAsRead($userId);
        }
    }

//=================================== bootstrap ====================================//
try{
    AuthRole::allowOnly(['registrar']);
    $controller = new NotificationsController($con);

    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])){
        if(!Csrf::isValid($_POST['csrf_token'] ?? null)){
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid session.']);
            exit();
        }

        $success = $controller->markAllAsRead($_SESSION['id']);
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit();
    }
}catch(Exception $e){
    ErrorHandler::log($e, 'NotificationsController (bootstrap)');
    http_response_code(500);
    echo json_encode(['success' => false]);
    exit();
}
