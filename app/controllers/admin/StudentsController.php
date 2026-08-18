<?php
session_start();

require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../../database/config/config.php';

    class StudentsController{
        private $model;

        public function __construct($con){
            $this->model = new StudentsModel($con);
        }

        public function index($page = 1){
            $limit = 10;
            $page = max(1, (int)$page);

            $totalRecords = (int)$this->model->getTotalCount();
            $totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;
            $page = min($page, $totalPages);
            $offset = ($page - 1) * $limit;

            return [
                'records'       => $this->model->getPaginated($limit, $offset) ?: [],
                'current_page'  => $page,
                'total_pages'   => $totalPages,
                'total_records' => $totalRecords,
                'limit'         => $limit,
            ];
        }
    }

try{
    $controller = new StudentsController($con);

    $page = $_GET['page'] ?? 1;

    $listing = $controller->index($page);
    $students       = $listing['records'];
    $current_page   = $listing['current_page'];
    $total_pages    = $listing['total_pages'];
    $total_records  = $listing['total_records'];
    $limit          = $listing['limit'];
}catch(Exception $e){
    ErrorHandler::log($e, 'StudentsController (bootstrap)');
    $students = [];
    $current_page = 1;
    $total_pages = 1;
    $total_records = 0;
    $limit = 10;
}
