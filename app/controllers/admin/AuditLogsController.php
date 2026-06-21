<?php
session_start();

require_once __DIR__ . '/../../models/admin/AuditLogsModel.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/csrf.php';

    class AuditLogsController{
        private $model;

        public function __construct($con){
            $this->model = new AuditLogsModel($con);
        }

        public function index($search = '', $page = 1){
            $limit = 10;
            $page = max(1, (int)$page);

            $totalRecords = $this->model->count($search);
            $totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;
            $page = min($page, $totalPages);
            $offset = ($page - 1) * $limit;

            return [
                'records'       => $this->model->index($search, $limit, $offset) ?: [],
                'current_page'  => $page,
                'total_pages'   => $totalPages,
                'total_records' => $totalRecords,
                'limit'         => $limit,
            ];
        }

        public function delete($id){
            if($this->model->delete($id)){
                FlashMessage::setFlash("success", "Log deleted successfully.");
                header("Location: ../../../resources/views/admin/audit-logs.php");
                exit();
            }else{
                FlashMessage::setFlash("error", "Error deleting log.");
                header("Location: ../../../resources/views/admin/audit-logs.php");
                exit();
            }
        }
    }

    try{
        $controller = new AuditLogsController($con);

        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_logs'])){
            Csrf::requireValidOnPost('../../../resources/views/admin/audit-logs.php');
            $log_id = $_POST['id'];
            $controller->delete($log_id);
        }

        $search_term = trim($_GET['search'] ?? '');
        $page = $_GET['page'] ?? 1;

        $listing = $controller->index($search_term, $page);
        $paginated_logs = $listing['records'];
        $current_page = $listing['current_page'];
        $total_pages = $listing['total_pages'];
        $total_entries = $listing['total_records'];
        $entries_per_page = $listing['limit'];
        $offset = ($current_page - 1) * $entries_per_page;
    }catch(Exception $e){
        error_log("Error " . $e->getMessage());
    }