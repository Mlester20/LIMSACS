<?php
session_start();

require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../models/registrar/DocumentTypesModel.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../../database/config/config.php';

    class DocumentTypesController extends Controller{
        protected $auditLogs;

        public function __construct($con){
            parent::__construct(
                new DocumentTypesModel($con)
            );
            $this->auditLogs = new AuditLogs($con);
        }

        public function index(){
            return $this->model->index();
        }

        public function create($data){
            try{
                if($this->model->create($data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'CREATE DOCUMENT TYPE',
                        'DOCUMENT_TYPE',
                        null,
                        'document_types',
                        $_SESSION['full_name'] . ' created a new document type: ' . $data['document_name'] . '.',
                    );
                    FlashMessage::setFlash("success", "Document type created successfully.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/document-types.php");
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Failed to create document type.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/document-types.php");
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                FlashMessage::setFlash('error', 'Failed to create document type. Please try again.');
            }
        }

        public function update($id, $data){
            try{
                if($this->model->update($id, $data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'UPDATE DOCUMENT TYPE',
                        'DOCUMENT_TYPE',
                        null,
                        'document_types',
                        $_SESSION['full_name'] . ' updated a document type with ID: ' . $id . '.',
                    );
                    FlashMessage::setFlash("success", "Document type updated successfully.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/document-types.php");
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Failed to update document type.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/document-types.php");
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                FlashMessage::setFlash('error', 'Failed to update document type. Please try again.');
            }
        }

        public function delete($id){
            try{
                if($this->model->delete($id)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'DELETE DOCUMENT TYPE',
                        'DOCUMENT_TYPE',
                        null,
                        'document_types',
                        $_SESSION['full_name'] . ' deleted a document type with ID: ' . $id . '.',
                    );
                    FlashMessage::setFlash("success", "Document type deleted successfully.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/document-types.php");
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Failed to delete document type.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/document-types.php");
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                FlashMessage::setFlash('error', 'Failed to delete document type. Please try again.');
                header("Location: " . BASE_URL . "/resources/views/registrar/document-types.php");
                exit();
            }
        }
    }


    try{
        $controller = new DocumentTypesController($con);
        $documentTypes = $controller->index();

        // Pagination
        $entries_per_page = 10;
        $total_entries = count($documentTypes);
        $total_pages = ceil($total_entries / $entries_per_page);
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $current_page = max(1, min($current_page, $total_pages));

        // Slice document types for current page
        $offset = ($current_page - 1) * $entries_per_page;
        $paginated_documentTypes = array_slice($documentTypes, $offset, $entries_per_page);

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/document-types.php');

            if(isset($_POST['save_document_type'])){
                $controller->create(
                    [
                        'document_name' => $_POST['document_name'],
                        'is_required' => isset($_POST['is_required']) ? 1 : 0,
                        'is_active' => isset($_POST['is_active']) ? 1 : 0
                    ]
                );
            }

            if(isset($_POST['update_document_type'])){
                $document_id = $_POST['document_id'];
                $controller->update(
                    $document_id,
                    [
                        'document_name' => $_POST['document_name'],
                        'is_required' => isset($_POST['is_required']) ? 1 : 0,
                        'is_active' => isset($_POST['is_active']) ? 1 : 0
                    ]
                );
            }

            if(isset($_POST['delete_document_type'])){
                $document_id = $_POST['document_id'];
                $controller->delete($document_id);
            
            }
        }
    }catch(Exception $e){
        error_log($e->getMessage());
        exit();
    }