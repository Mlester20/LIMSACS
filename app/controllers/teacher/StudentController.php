<?php
session_start();

require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../models/teacher/StudentsModel.php';
require_once __DIR__ . '/../../models/registrar/AcademicHistoryModel.php';
require_once __DIR__ . '/../../models/registrar/ParentGuardiansModel.php';
require_once __DIR__ . '/../../models/registrar/StudentsDocumentModel.php';
require_once __DIR__ . '/../../models/registrar/DocumentTypesModel.php';
require_once __DIR__ . '/../../services/StudentsService.php';
require_once __DIR__ . '/../../services/EnrollmentService.php';

    class StudentController{
        private $itemsPerPage = 10;
        private $model;
        private $academicHistory;
        private $parentGuardians;
        private $studentsDocuments;
        private $documentTypes;
        private $enrollmentService;
        protected $auditLogs;

        public function __construct($con){
            $this->model = new TeacherStudentsModel($con);
            $this->academicHistory = new AcademicHistoryModel($con);
            $this->parentGuardians = new ParentGuardiansModel($con);
            $this->studentsDocuments = new StudentsDocumentModel($con);
            $this->documentTypes = new DocumentTypesModel($con);
            $this->enrollmentService = new EnrollmentService($con);
            $this->auditLogs = new AuditLogs($con);
        }

        /**
         * Get paginated students assigned to this teacher
         * @param int $teacher_id
         * @param int $page
         * @return array Pagination data with students
         */
        public function index($teacher_id, $page = 1){
            $page = max(1, intval($page));
            $offset = ($page - 1) * $this->itemsPerPage;

            $students = $this->model->getPaginated($teacher_id, $this->itemsPerPage, $offset);
            $studentsWithFullNames = StudentsService::addFullNames($students);

            $totalCount = $this->model->getTotalCount($teacher_id);
            $totalPages = max(1, ceil($totalCount / $this->itemsPerPage));

            return [
                'students' => $studentsWithFullNames,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalCount,
                'itemsPerPage' => $this->itemsPerPage,
                'hasNextPage' => $page < $totalPages,
                'hasPrevPage' => $page > 1
            ];
        }

        /**
         * Get a student's full profile, scoped to students assigned to this teacher
         * @param int $student_id
         * @param int $teacher_id
         * @return array
         */
        public function getStudentProfile($student_id, $teacher_id){
            $empty = [
                'student' => null,
                'academic_history' => [],
                'parent_guardians' => [],
                'student_documents' => [],
                'document_types' => []
            ];

            try{
                if(!$this->model->isStudentOwnedByTeacher($student_id, $teacher_id)){
                    return $empty;
                }

                $student = $this->model->getById($student_id);
                $academicHistory = $this->academicHistory->getByStudentId($student_id);
                $parentGuardians = $this->parentGuardians->getByStudentId($student_id);
                $studentDocuments = $this->studentsDocuments->getByStudentId($student_id);
                $documentTypes = $this->documentTypes->index();

                return [
                    'student' => $student,
                    'academic_history' => $academicHistory ?: [],
                    'parent_guardians' => $parentGuardians ?: [],
                    'student_documents' => $studentDocuments ?: [],
                    'document_types' => $documentTypes ?: []
                ];
            }catch(Exception $e){
                ErrorHandler::log($e, 'teacher/StudentController::getStudentProfile');
                return $empty;
            }
        }

        /**
         * Update a student's personal information, scoped to students assigned to this teacher
         * @param int $id
         * @param array $data
         * @param int $teacher_id
         * @param int $page
         */
        public function update($id, $data, $teacher_id, $page = 1){
            try{
                if(!$this->model->isStudentOwnedByTeacher($id, $teacher_id)){
                    FlashMessage::setFlash("error", "You do not have permission to update this student.");
                    header("Location: " . BASE_URL . "/resources/views/teachers/students.php?page=" . intval($page));
                    exit();
                }

                if($this->model->update($id, $data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'UPDATE STUDENT',
                        'STUDENT',
                        $id,
                        'students',
                        ($_SESSION['full_name'] ?? 'A teacher') . ' updated student record with ID: ' . $id
                    );
                    FlashMessage::setFlash("success", "Student record updated successfully.");
                }else{
                    FlashMessage::setFlash("error", "Failed to update student record. Please try again.");
                }
                header("Location: " . BASE_URL . "/resources/views/teachers/students.php?page=" . intval($page));
                exit();
            }catch(Exception $e){
                ErrorHandler::log($e, 'teacher/StudentController::update');
                return false;
            }
        }

        /**
         * Mark a student's enrollment as Dropped or Transferred, scoped to students assigned to this teacher
         * @param int $enrollment_id academic_history.id
         * @param string $new_status 'Dropped' | 'Transferred'
         * @param int $teacher_id
         * @return array ['success' => bool, 'message' => string]
         */
        public function updateStudentStatus($enrollment_id, $new_status, $teacher_id){
            $enrollment = $this->model->getEnrollmentOwnedByTeacher($enrollment_id, $teacher_id);
            if(!$enrollment){
                return ['success' => false, 'message' => 'You do not have permission to update this student.'];
            }

            $result = $this->enrollmentService->updateStudentStatus($enrollment_id, $new_status, null, $teacher_id);

            if($result['success']){
                $actionMap = [
                    'Dropped' => 'DROP STUDENT',
                    'Transferred' => 'TRANSFER STUDENT'
                ];

                $this->auditLogs->log(
                    $_SESSION['id'] ?? null,
                    $_SESSION['role'] ?? 'unknown',
                    $actionMap[$new_status] ?? strtoupper($new_status) . ' STUDENT',
                    'ENROLLMENT',
                    $enrollment_id,
                    'academic_history',
                    "Marked {$result['studentName']} as {$new_status}"
                );
            }

            return $result;
        }
    }

    //=================================== boostrap the controller ====================================//
    try{
        $teacher_id = $_SESSION['id'] ?? 0;
        $controller = new StudentController($con);
        $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $paginationData = $controller->index($teacher_id, $currentPage);
        $students = $paginationData['students'];
        $pagination = [
            'currentPage' => $paginationData['currentPage'],
            'totalPages' => $paginationData['totalPages'],
            'totalRecords' => $paginationData['totalRecords'],
            'itemsPerPage' => $paginationData['itemsPerPage'],
            'hasNextPage' => $paginationData['hasNextPage'],
            'hasPrevPage' => $paginationData['hasPrevPage']
        ];

        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])){
            Csrf::requireValidOnPost(BASE_URL . '/resources/views/teachers/students.php');

            $studentId = $_POST['student_id'];
            $controller->update(
                $studentId,
                [
                    "lrn" => $_POST['lrn'],
                    "first_name" => $_POST['first_name'],
                    "middle_name" => $_POST['middle_name'],
                    "last_name" => $_POST['last_name'],
                    "suffix" => $_POST['suffix'],
                    "gender" => $_POST['gender'],
                    "birth_date" => $_POST['birth_date'],
                    "age" => $_POST['age'],
                    "place_of_birth" => $_POST['place_of_birth'],
                    "nationality" => $_POST['nationality'],
                    "religion" => $_POST['religion'],
                    "address" => $_POST['address'],
                    "contact_number" => $_POST['contact_number']
                ],
                $teacher_id,
                $currentPage
            );
        }
    }catch(Exception $e){
        ErrorHandler::log($e, 'teacher/StudentController (bootstrap)');
        exit();
    }

    //=================================== Handle AJAX requests ====================================//
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
        $controller = new StudentController($con);
        $teacher_id = $_SESSION['id'] ?? 0;
        header('Content-Type: application/json');

        if($_POST['action'] === 'get_student_profile'){
            $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
            $profile = $controller->getStudentProfile($student_id, $teacher_id);
            echo json_encode($profile);
            exit();
        }

        if($_POST['action'] === 'update_status'){
            if(!Csrf::isValid($_POST['csrf_token'] ?? null)){
                echo json_encode(['success' => false, 'message' => 'Your session has expired. Please refresh the page and try again.']);
                exit();
            }

            $enrollment_id = $_POST['enrollment_id'] ?? null;
            $new_status = $_POST['new_status'] ?? null;
            $allowedStatuses = ['Dropped', 'Transferred'];

            if(!$enrollment_id || !in_array($new_status, $allowedStatuses, true)){
                echo json_encode(['success' => false, 'message' => 'Missing or invalid status update data.']);
                exit();
            }

            $result = $controller->updateStudentStatus($enrollment_id, $new_status, $teacher_id);
            echo json_encode($result);
            exit();
        }
    }
?>
