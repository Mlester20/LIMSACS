<?php
session_start();

require_once __DIR__ . '/../../models/registrar/StudentsDocumentModel.php';
require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../models/registrar/DocumentTypesModel.php';
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/fileUpload.php';
require_once __DIR__ . '/../../helpers/FlashMessage.php';
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
                    header('Location: ../../../resources/views/registrar/student-documents.php');
                    exit();
               }else{
                    FlashMessage::setFlash("error", "Failed to upload document");
                    header("Location: ../../../resources/views/registrar/student-documents.php");
                    exit();
               }
          }catch(Exception $e){
               error_log($e->getMessage());
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
                    header('Location: ../../../resources/views/registrar/student-documents.php');
                    exit();
               }else{
                    FlashMessage::setFlash("error", "Failed to update document");
                    header("Location: ../../../resources/views/registrar/student-documents.php");
                    exit();
               }
          }catch(Exception $e){
               error_log($e->getMessage());
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
                    header('Location: ../../../resources/views/registrar/student-documents.php');
                    exit();
               }else{
                    FlashMessage::setFlash("error", "Failed to delete document");
                    header("Location: ../../../resources/views/registrar/student-documents.php");
                    exit();
               }
          }catch(Exception $e){
               error_log($e->getMessage());
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

     $documentTypes = $controller->getDocumentTypes();

     if($_SERVER['REQUEST_METHOD'] === 'POST'){
          if(isset($_POST['submit_document'])){
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
     error_log($e->getMessage());
     FlashMessage::setFlash('error', 'An error occurred while processing your request.');
   }