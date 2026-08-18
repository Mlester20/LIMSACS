<?php
session_start();

require_once __DIR__ . '/../../models/registrar/ParentGuardiansModel.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../../database/config/config.php';

    class ParentGuardiansController{
        private $model;

        public function __construct($con){
            $this->model = new ParentGuardiansModel($con);
        }

        public function index($page = 1){
            $limit = 10;
            $page = max(1, (int)$page);

            $totalRecords = (int)$this->model->getTotalCount();
            $totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;
            $page = min($page, $totalPages);
            $offset = ($page - 1) * $limit;

            return [
                'records'       => $this->model->getWithPagination($offset, $limit) ?: [],
                'current_page'  => $page,
                'total_pages'   => $totalPages,
                'total_records' => $totalRecords,
                'limit'         => $limit,
            ];
        }
    }

try{
    $controller = new ParentGuardiansController($con);

    $page = $_GET['page'] ?? 1;

    $listing = $controller->index($page);
    $parent_guardians  = $listing['records'];
    $current_page      = $listing['current_page'];
    $total_pages       = $listing['total_pages'];
    $total_records     = $listing['total_records'];
    $limit             = $listing['limit'];
}catch(Exception $e){
    ErrorHandler::log($e, 'ParentGuardiansController (bootstrap)');
    $parent_guardians = [];
    $current_page = 1;
    $total_pages = 1;
    $total_records = 0;
    $limit = 10;
}
