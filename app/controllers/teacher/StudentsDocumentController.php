<?php
session_start();

require_once __DIR__ . '/../../models/teacher/TeacherModel.php';
require_once __DIR__ . '/../../models/registrar/StudentsDocumentModel.php';
require_once __DIR__ . '/../../models/registrar/DocumentTypesModel.php';
require_once __DIR__ . '/../../helpers/fileUpload.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/../../models/NotificationsModel.php';

    /**
     * A teacher can only ADD and VIEW documents for students in the section
     * they advise -- never edit status, verify, reject, or delete. Every
     * document a teacher uploads is forced to "Pending" status server-side,
     * regardless of anything posted, since a teacher isn't the one who
     * verifies requirements -- that stays the registrar's job.
     */
    class TeacherStudentsDocumentController{
        private $teacherModel;
        private $documentsModel;
        private $documentTypesModel;
        private $auditLogs;
        private $notificationsModel;

        public function __construct($con){
            $this->teacherModel = new TeacherModel($con);
            $this->documentsModel = new StudentsDocumentModel($con);
            $this->documentTypesModel = new DocumentTypesModel($con);
            $this->auditLogs = new AuditLogs($con);
            $this->notificationsModel = new NotificationsModel($con);
        }

        /**
         * @param int $teacherId
         * @return array ['section'=>, 'students'=>, 'documents'=>, 'document_types'=>]
         */
        public function index($teacherId){
            $section = $this->teacherModel->getAssignedSection($teacherId);
            if(!$section){
                return ['section' => null, 'students' => [], 'documents' => [], 'document_types' => []];
            }

            $students = $this->teacherModel->getMyStudents($section['id'], $section['school_year_id']);
            $studentIds = array_map(fn($s) => (int) $s['student_id'], $students);

            return [
                'section' => $section,
                'students' => $students,
                'documents' => $this->documentsModel->getByStudentIds($studentIds),
                'document_types' => $this->documentTypesModel->index(),
            ];
        }

        /**
         * @param int $teacherId
         * @param array $data
         * @return array ['success'=>bool, 'message'=>string]
         */
        public function upload($teacherId, $data){
            $section = $this->teacherModel->getAssignedSection($teacherId);
            if(!$section){
                return ['success' => false, 'message' => 'You are not currently assigned as an adviser.'];
            }

            // Only ever allow uploading for one of THIS teacher's own students --
            // never trust the posted student_id alone.
            $students = $this->teacherModel->getMyStudents($section['id'], $section['school_year_id']);
            $allowedStudentIds = array_map(fn($s) => (int) $s['student_id'], $students);
            $studentId = (int) ($data['student_id'] ?? 0);

            if(!in_array($studentId, $allowedStudentIds, true)){
                return ['success' => false, 'message' => 'That student is not in your advised section.'];
            }

            $payload = [
                'student_id' => $studentId,
                'document_type_id' => $data['document_type_id'] ?? null,
                'file_path' => $data['file_path'] ?? null,
                'status' => 'Pending', // always, regardless of anything posted
                'remarks' => null,
                'uploaded_by' => $teacherId,
            ];

            if($this->documentsModel->create($payload)){
                $this->auditLogs->log(
                    $teacherId,
                    'teacher',
                    'UPLOAD DOCUMENT',
                    'STUDENTS_DOCUMENTS',
                    null,
                    'students_documents',
                    ($_SESSION['full_name'] ?? 'Teacher') . ' uploaded a document for student ID: ' . $studentId,
                );

                $student = current(array_filter($students, fn($s) => (int) $s['student_id'] === $studentId));
                $studentName = $student ? $student['last_name'] . ', ' . $student['first_name'] : 'a student';
                $documentType = current(array_filter($this->documentTypesModel->index(), fn($t) => (int) $t['id'] === (int) $payload['document_type_id']));
                $documentName = $documentType ? $documentType['document_name'] : 'a document';

                $this->notificationsModel->notifyRole('registrar', [
                    'type' => 'document_upload',
                    'title' => 'New document uploaded',
                    'message' => ($_SESSION['full_name'] ?? 'A teacher') . " uploaded \"{$documentName}\" for {$studentName}.",
                    'link' => BASE_URL . '/resources/views/registrar/student-documents.php?status=Pending',
                    'reference_table' => 'students_documents',
                    'reference_id' => null,
                ]);

                return ['success' => true, 'message' => 'Document uploaded. It is now Pending review by the registrar.'];
            }

            return ['success' => false, 'message' => 'Failed to upload document. Please try again.'];
        }
    }

    //=================================== bootstrap ====================================//
    try{
        AuthRole::allowOnly(['teacher']);
        $controller = new TeacherStudentsDocumentController($con);

        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])){
            Csrf::requireValidOnPost(BASE_URL . '/resources/views/teacher/documents.php');

            $result = $controller->upload($_SESSION['id'], [
                'student_id' => $_POST['student_id'] ?? null,
                'document_type_id' => $_POST['document_type_id'] ?? null,
                'file_path' => FileUpload::upload($_FILES['file_path'], 'student_documents', 'doc'),
            ]);

            FlashMessage::setFlash($result['success'] ? 'success' : 'error', $result['message']);
            header('Location: ' . BASE_URL . '/resources/views/teacher/documents.php');
            exit();
        }

        $teacherDocumentsData = $controller->index($_SESSION['id']);
    }catch(Exception $e){
        ErrorHandler::log($e, 'TeacherStudentsDocumentController (bootstrap)');
        $teacherDocumentsData = ['section' => null, 'students' => [], 'documents' => [], 'document_types' => []];
    }
