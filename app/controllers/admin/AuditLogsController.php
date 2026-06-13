<?php
session_start();

require_once __DIR__ . '/../../models/admin/AuditLogsModel.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';

    class AuditLogsController{
        private $model;

        public function __construct($con){
            $this->model = new AuditLogsModel($con);
        }

        public function index(){
            return $this->model->index();
        }

        public function delete($id){
            if($this->model->delete($id)){
                FlashMessage::setFlash("success", "Log deleted successfully.");
                header("Location: ../../../resources/views/admin/audit-logs.php");
                exit();
            }else{
                FlashMessage::setFlash("success", "Error deleting log.");
                header("Location: ../../../resources/views/admin/audit-logs.php");
                exit();                
            }
        }
    }

    try{
        $controller = new AuditLogsController($con);
        $logs = $controller->index();

        //pagination
        $entries_per_page = 10;
        $total_entries = count($logs);
        $total_pages = ceil($total_entries / $entries_per_page);
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $current_page = max(1, min($current_page, $total_pages));

        // Slice logs for current page
        $offset = ($current_page - 1) * $entries_per_page;
        $paginated_logs = array_slice($logs, $offset, $entries_per_page);

        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_logs'])){
            $log_id = $_POST['id'];
            $controller->delete($log_id);
        }
    }catch(Exception $e){
        error_log("Error " . $e->getMessage());
    }