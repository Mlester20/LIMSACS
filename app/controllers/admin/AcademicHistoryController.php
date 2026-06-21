<?php
session_start();

require_once __DIR__ . '/../../models/admin/AcademicHistoryModel.php';
require_once __DIR__ . '/../../../database/config/config.php';

    class AcademicHistoryController{
        private $model;

        public function __construct($con){
            $this->model = new AcademicHistoryModel($con);
        }

        /**
         * Handle the request to display the academic history page.
         * Retrieves paginated academic history records plus pagination metadata.
         */
        public function index($search = '', $schoolYearId = ''){
            $limit = 10; // Number of records per page
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = $page < 1 ? 1 : $page; // guard against 0/negative page values

            $totalRecords = $this->model->getTotalCount($search, $schoolYearId);
            $totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;

            // Clamp page so it never exceeds the last available page
            $page = $page > $totalPages ? $totalPages : $page;
            $offset = ($page - 1) * $limit;

            $records = $this->model->getPaginated($limit, $offset, $search, $schoolYearId);

            return [
                'records'       => $records ?: [],
                'current_page'  => $page,
                'total_pages'   => $totalPages,
                'total_records' => $totalRecords,
                'limit'         => $limit,
            ];
        }
    }

try{
    $controller = new AcademicHistoryController($con);

    $search_term = trim($_GET['search'] ?? '');
    $school_year_filter = $_GET['school_year_id'] ?? '';

    $pagination_result = $controller->index($search_term, $school_year_filter);

    $academic_histories = $pagination_result['records'];
    $current_page       = $pagination_result['current_page'];
    $total_pages         = $pagination_result['total_pages'];
    $total_records       = $pagination_result['total_records'];
    $limit               = $pagination_result['limit'];

    // For the school-year filter dropdown
    $school_year_options = $con->query("SELECT id, school_year FROM school_year ORDER BY school_year DESC")->fetch_all(MYSQLI_ASSOC);
}catch(Exception $e){
    error_log($e->getMessage());
    $academic_histories = [];
    $current_page = 1;
    $total_pages = 1;
    $total_records = 0;
    $limit = 10;
    $search_term = '';
    $school_year_filter = '';
    $school_year_options = [];
}