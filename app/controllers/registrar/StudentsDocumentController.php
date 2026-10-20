<?php
session_start();

require_once __DIR__ . '/../../models/registrar/StudentsDocumentModel.php';
require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../models/registrar/DocumentTypesModel.php';
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/fileUpload.php';
require_once __DIR__ . '/../../helpers/FlashMessage.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../services/StudentsService.php';
require_once __DIR__ . '/../../../database/config/config.php';

   class StudentsDocumentController extends Controller{
     protected $students;
     protected $documentTypes;
     protected $auditLogs;
     protected $studentsService;

     public function __construct($con){
          parent::__construct(
               new StudentsDocumentModel($con)
          );
          $this->students = new StudentsModel($con);
          $this->documentTypes = new DocumentTypesModel($con);
          $this->auditLogs = new AuditLogs($con);
          $this->studentsService = new StudentsService($con);
     }


     public function index(){
          return $this->model->index();
     }

     public function getDocumentTypes(){
          return $this->documentTypes->index();
     }
     
     public function searchStudents($keyword){
          return $this->studentsService->searchStudents($keyword);
     }

     /**
      * All documents matching a given status (Pending/Verified/Rejected/Submitted),
      * student/document-type names already joined in. Powers the dashboard's
      * "click a status card -> see the list, then print" modal.
      * @param string $status
      * @return array
      */
     public function getByStatus($status){
          return $this->model->getPaginated(1000, 0, $status);
     }

     /**
      * Required document types a student hasn't submitted yet. Powers the
      * "click a student's name" missing-documents modal.
      * @param int $student_id
      * @return array
      */
     public function getMissingDocuments($student_id){
          return $this->model->getMissingRequiredDocuments($student_id);
     }

     public function create($data){
          try{
               if($this->model->create($data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'CREATE DOCUMENT',
                        'STUDENTS_DOCUMENTS',
                        null,
                        'students_documents',
                        $_SESSION['full_name'] . ' created a new student document for student ID: ' . $data['student_id'],
                    );
                    FlashMessage::setFlash('success', 'Document uploaded successfully');
                    header('Location: ' . BASE_URL . '/resources/views/registrar/student-documents.php');
                    exit();
               }else{
                    FlashMessage::setFlash("error", "Failed to upload document");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-documents.php");
                    exit();
               }
          }catch(Exception $e){
               ErrorHandler::log($e, 'StudentsDocumentController::create');
               return false;
          }
     }

     public function update($id, $data){
          try{
               // If no new file was uploaded, keep the existing file_path instead of
               // overwriting it with null.
               if(empty($data['file_path'])){
                    $existing = $this->model->find($id);
                    $data['file_path'] = $existing['file_path'] ?? null;
               }

               if($this->model->update($id, $data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'UPDATE DOCUMENT',
                        'STUDENTS_DOCUMENTS',
                        null,
                        'students_documents',
                        $_SESSION['full_name'] . ' updated student document with ID: ' . $id,
                    );
                    FlashMessage::setFlash('success', 'Document updated successfully');
                    header('Location: ' . BASE_URL . '/resources/views/registrar/student-documents.php');
                    exit();
               }else{
                    FlashMessage::setFlash("error", "Failed to update document");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-documents.php");
                    exit();
               }
          }catch(Exception $e){
               ErrorHandler::log($e, 'StudentsDocumentController::update');
               return false;
          }
     }

     /**
      * Quick "mark as Verified" action from the Actions column, bypassing the
      * full Edit modal since status is the only thing changing.
      * @param int $id
      */
     public function verifyDocument($id){
          try{
               if($this->model->updateStatus($id, 'Verified')){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'VERIFY DOCUMENT',
                        'STUDENTS_DOCUMENTS',
                        null,
                        'students_documents',
                        $_SESSION['full_name'] . ' marked student document ID: ' . $id . ' as Verified',
                    );
                    FlashMessage::setFlash('success', 'Document marked as Verified.');
                    header('Location: ' . BASE_URL . '/resources/views/registrar/student-documents.php');
                    exit();
               }else{
                    FlashMessage::setFlash("error", "Failed to update document status.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-documents.php");
                    exit();
               }
          }catch(Exception $e){
               ErrorHandler::log($e, 'StudentsDocumentController::verifyDocument');
               return false;
          }
     }

     // For document deletion, we also want to delete the file from storage
     public function delete($id){
          try{
               // Fetch the document BEFORE deleting the row, otherwise file_path is gone.
               $document = $this->model->find($id);

               if($this->model->delete($id)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'DELETE DOCUMENT',
                        'STUDENTS_DOCUMENTS',
                        null,
                        'students_documents',
                        $_SESSION['full_name'] . ' deleted a student document with ID: ' . $id,
                    );
                    if($document && isset($document['file_path'])){
                         FileUpload::delete($document['file_path']);
                    }
                    FlashMessage::setFlash('success', 'Document deleted successfully');
                    header('Location: ' . BASE_URL . '/resources/views/registrar/student-documents.php');
                    exit();
               }else{
                    FlashMessage::setFlash("error", "Failed to delete document");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-documents.php");
                    exit();
               }
          }catch(Exception $e){
               ErrorHandler::log($e, 'StudentsDocumentController::delete');
               return false;
          }
     }
   }
   

   // ============== Bootstrap the controller ==============

   try{
     $controller = new StudentsDocumentController($con);

     // ---- AJAX: student search (powers the autocomplete in the add/edit modal) ----
     // GET /student-documents.php?action=search_students&keyword=...
     if($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? null) === 'search_students'){
          header('Content-Type: application/json');
          echo json_encode($controller->searchStudents($_GET['keyword'] ?? ''));
          exit();
     }

     // ---- AJAX: documents by status (powers the dashboard's status-card modal) ----
     // GET /student-documents.php?action=get_by_status&status=Pending
     if($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? null) === 'get_by_status'){
          header('Content-Type: application/json');
          $allowedStatuses = ['Submitted', 'Verified', 'Pending', 'Rejected'];
          $status = $_GET['status'] ?? '';
          if(!in_array($status, $allowedStatuses, true)){
               echo json_encode(['success' => false, 'message' => 'Invalid status.']);
               exit();
          }
          echo json_encode(['success' => true, 'data' => $controller->getByStatus($status)]);
          exit();
     }

     // ---- AJAX: missing required documents for a student (click a student's name) ----
     // GET /student-documents.php?action=missing_documents&student_id=123
     if($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? null) === 'missing_documents'){
          header('Content-Type: application/json');
          $studentId = filter_var($_GET['student_id'] ?? null, FILTER_VALIDATE_INT);
          if(!$studentId){
               echo json_encode(['success' => false, 'message' => 'Invalid student.']);
               exit();
          }
          echo json_encode(['success' => true, 'data' => $controller->getMissingDocuments($studentId)]);
          exit();
     }

     $documentTypes = $controller->getDocumentTypes();

     if($_SERVER['REQUEST_METHOD'] === 'POST'){
          if(isset($_POST['submit_document']) || isset($_POST['update_document']) || isset($_POST['delete_document']) || isset($_POST['verify_document'])){
               Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/student-documents.php');
          }

          if(isset($_POST['verify_document'])){
               $controller->verifyDocument($_POST['document_id'] ?? null);
          }

          if(isset($_POST['submit_document'])){
               if(($_POST['status'] ?? '') === 'Rejected' && trim($_POST['remarks'] ?? '') === ''){
                    FlashMessage::setFlash('error', 'Remarks are required when a document is marked as Rejected.');
                    header('Location: ' . BASE_URL . '/resources/views/registrar/student-documents.php');
                    exit();
               }
               $controller->create(
                    [
                         'student_id' => $_POST['student_id'] ?? null,
                         'document_type_id' => $_POST['document_type_id'] ?? null,
                         'file_path' => FileUpload::upload($_FILES['file_path'], 'student_documents', 'doc'),
                         // Default status when none is supplied is 'Submitted', not 'Pending'.
                         'status' => $_POST['status'] ?? 'Submitted',
                         'remarks' => $_POST['remarks'] ?? null,
                         // Renamed from 'created_by' to 'uploaded_by' to match the
                         // students_documents table column and StudentsDocumentModel::create().
                         'uploaded_by' => $_SESSION['id'] ?? null,
                    ]
               );
          }

          if(isset($_POST['update_document'])){
               if(($_POST['status'] ?? '') === 'Rejected' && trim($_POST['remarks'] ?? '') === ''){
                    FlashMessage::setFlash('error', 'Remarks are required when a document is marked as Rejected.');
                    header('Location: ' . BASE_URL . '/resources/views/registrar/student-documents.php');
                    exit();
               }
               $document_id = $_POST['document_id'] ?? null;
               $controller->update(
                    $document_id,
                    [
                         'student_id' => $_POST['student_id'] ?? null,
                         'document_type_id' => $_POST['document_type_id'] ?? null,
                         // For file update, we check if a new file is uploaded
                         'file_path' => isset($_FILES['file_path']) && $_FILES['file_path']['error'] === UPLOAD_ERR_OK
                                        ? FileUpload::upload($_FILES['file_path'], 'student_documents', 'doc')
                                        : null, // No new file -> update() keeps the existing path
                         'status' => $_POST['status'] ?? 'Submitted',
                         'remarks' => $_POST['remarks'] ?? null,
                         'uploaded_by' => $_SESSION['id'] ?? null,
                    ]
               );
          }

          if(isset($_POST['delete_document'])){
               $document_id = $_POST['document_id'] ?? null;
               $controller->delete($document_id);
          }
     }
   }catch(Exception $e){
     ErrorHandler::log($e, 'StudentsDocumentController (bootstrap)');
     FlashMessage::setFlash('error', 'An error occurred while processing your request.');
   }