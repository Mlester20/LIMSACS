<?php
session_start();

require_once __DIR__ . '/../../models/registrar/StudentsDocumentModel.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../../database/config/config.php';

    class StudentsDocumentController{
        private $model;

        public function __construct($con){
            $this->model = new StudentsDocumentModel($con);
        }

        public function index($page = 1, $status = ''){
            $limit = 10;
            $page = max(1, (int)$page);

            $totalRecords = (int)$this->model->getTotalCount($status);
            $totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;
            $page = min($page, $totalPages);
            $offset = ($page - 1) * $limit;

            return [
                'records'       => $this->model->getPaginated($limit, $offset, $status) ?: [],
                'current_page'  => $page,
                'total_pages'   => $totalPages,
                'total_records' => $totalRecords,
                'limit'         => $limit,
            ];
        }
    }

try{
    $controller = new StudentsDocumentController($con);

    $page = $_GET['page'] ?? 1;
    $status_filter = $_GET['status'] ?? '';

    $listing = $controller->index($page, $status_filter);
    $documents      = $listing['records'];
    $current_page   = $listing['current_page'];
    $total_pages    = $listing['total_pages'];
    $total_records  = $listing['total_records'];
    $limit          = $listing['limit'];
}catch(Exception $e){
    ErrorHandler::log($e, 'StudentsDocumentController (bootstrap)');
    $documents = [];
    $current_page = 1;
    $total_pages = 1;
    $total_records = 0;
    $limit = 10;
    $status_filter = '';
}
