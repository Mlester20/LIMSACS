<?php
session_start();

require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../models/registrar/AcademicHistoryModel.php';
require_once __DIR__ . '/../../models/registrar/SectionsModel.php';
require_once __DIR__ . '/../../models/registrar/ParentGuardiansModel.php';
require_once __DIR__ . '/../../models/registrar/StudentsDocumentModel.php';
require_once __DIR__ . '/../../models/registrar/DocumentTypesModel.php';
require_once __DIR__ . '/../../services/StudentsService.php';
require_once __DIR__ . '/../../helpers/csrf.php';

    class StudentsController extends Controller{
        private $itemsPerPage = 10;
        protected $academicHistory;
        protected $parentGuardians;
        protected $studentsDocuments;
        protected $documentTypes;
        protected $auditLogs;
        protected $sections;

        public function __construct($con){
            parent::__construct(
                new StudentsModel($con)
            );
            $this->academicHistory = new AcademicHistoryModel($con);
            $this->parentGuardians = new ParentGuardiansModel($con);
            $this->studentsDocuments = new StudentsDocumentModel($con);
            $this->documentTypes = new DocumentTypesModel($con);
            $this->auditLogs = new AuditLogs($con);
            $this->sections = new SectionsModel($con);
        }

        /**
         * Get paginated students with pagination metadata
         * @param int $page Current page number
         * @param string $gradeLevel Filter by grade level, or '' for all
         * @param string|int $sectionId Filter by section, or '' for all
         * @param string $archivedFilter '0' = active only (default), '1' = archived only, 'all' = both
         * @param string $gender 'Male' | 'Female', or '' for all
         * @return array Pagination data with students
         */
        public function index($page = 1, $gradeLevel = '', $sectionId = '', $archivedFilter = '0', $gender = ''){
            // Ensure page is at least 1
            $page = max(1, intval($page));

            // Calculate offset
            $offset = ($page - 1) * $this->itemsPerPage;

            // Get paginated students
            $students = $this->model->getPaginated($this->itemsPerPage, $offset, $gradeLevel, $sectionId, $archivedFilter, $gender);

            // Add full names
            $studentsWithFullNames = StudentsService::addFullNames($students);

            // Get total count
            $totalCount = $this->model->getTotalCount($gradeLevel, $sectionId, $archivedFilter, $gender);
            $totalPages = ceil($totalCount / $this->itemsPerPage);

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
         * Bulk archive/unarchive a set of student ids.
         * @param array $ids
         * @param bool $archived
         * @param int $page
         */
        public function archiveStudents($ids, $archived, $page = 1){
            try{
                $ids = array_values(array_filter(array_map('intval', (array)$ids)));
                if(empty($ids)){
                    FlashMessage::setFlash("error", "Please select at least one student.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php?page=" . intval($page));
                    exit();
                }

                if($this->model->bulkSetArchived($ids, $archived)){
                    $action = $archived ? 'ARCHIVE STUDENTS' : 'UNARCHIVE STUDENTS';
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        $action,
                        'STUDENT',
                        null,
                        'students',
                        $_SESSION['full_name'] . ' ' . ($archived ? 'archived' : 'unarchived') . ' ' . count($ids) . ' student(s): ' . implode(', ', $ids),
                    );
                    FlashMessage::setFlash("success", count($ids) . ($archived ? ' student(s) archived.' : ' student(s) unarchived.'));
                }else{
                    FlashMessage::setFlash("error", "Failed to update the selected students. Please try again.");
                }
                header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php?page=" . intval($page));
                exit();
            }catch(Exception $e){
                ErrorHandler::log($e, 'StudentsController::archiveStudents');
                exit();
            }
        }

        /**
         * Filter dropdown options for the Student Data listing.
         * @return array{grade_levels: array, sections: array}
         */
        public function getFilterOptions(){
            return [
                'grade_levels' => $this->sections->getDistinctGradeLevels(),
                'sections' => $this->sections->index(),
            ];
        }

        /**
         * Get lrnExists
         * @param string $lrn
         */

        public function lrnExists($lrn){
            return $this->model->lrnExists($lrn);
        }

        public function create($data){
            try{
                if((int)($data['age'] ?? 0) < 1){
                    FlashMessage::setFlash("error", "Invalid age. The student's age cannot be 0.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php");
                    exit();
                }

                if(!empty($data['lrn']) && !preg_match('/^\d{1,12}$/', $data['lrn'])){
                    FlashMessage::setFlash("error", "LRN must contain numbers only, up to 12 digits.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php");
                    exit();
                }

                // Check if LRN exists
                if($this->lrnExists($data['lrn'])){
                    FlashMessage::setFlash("error", "LRN already exists. Please use a unique LRN.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php");
                    exit();
                }
                if($this->model->create($data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'ADD STUDENT',
                        'STUDENT',
                        null,
                        'students',
                        $_SESSION['full_name'] . ' added student record',
                    );
                    FlashMessage::setFlash("success", "Student added successfully.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php");
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Failed to enroll student. Please try again.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php");
                    exit();
                }
            }catch(Exception $e){
                ErrorHandler::log($e, 'StudentsController::create');
                return false;
            }
        }


        public function update($id, $data, $page = 1){
            try{
                if((int)($data['age'] ?? 0) < 1){
                    FlashMessage::setFlash("error", "Invalid age. The student's age cannot be 0.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php?page=" . intval($page));
                    exit();
                }

                if(!empty($data['lrn']) && !preg_match('/^\d{1,12}$/', $data['lrn'])){
                    FlashMessage::setFlash("error", "LRN must contain numbers only, up to 12 digits.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php?page=" . intval($page));
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
                        $_SESSION['full_name'] . ' updated student record with ID: ' . $id,
                    );
                    FlashMessage::setFlash("success", "Student record updated successfully.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php?page=" . intval($page));
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Failed to update student record. Please try again.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php?page=" . intval($page));
                    exit();
                }
            }catch(Exception $e){
                ErrorHandler::log($e, 'StudentsController::update');
                return false;
            }
        }

        public function delete($id, $page = 1){
           try{
                if($this->model->delete($id)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'DELETE STUDENT',
                        'STUDENT',
                        $id,
                        'students',
                        $_SESSION['full_name'] . ' deleted student record with ID: ' . $id,
                    );
                    FlashMessage::setFlash("success", "Student record deleted successfully.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php?page=" . intval($page));
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Failed to delete student record. Please try again.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/student-records.php?page=" . intval($page));
                    exit();
                }
           }catch(Exception $e){
               ErrorHandler::log($e, 'StudentsController::delete');
               return false;
           }
        }

        /**
         * Get student profile with academic history and parents/guardians
         * @param int $student_id Student ID
         * @return array Student profile data
         */
        public function getStudentProfile($student_id) {
            try {
                $academicHistoryModel = $this->academicHistory;
                $parentGuardiansModel = $this->parentGuardians;

                // Get student data
                $student = $this->model->getById($student_id);

                // Get academic history
                $academicHistory = $academicHistoryModel->getByStudentId($student_id);

                // Get parents/guardians
                $parentGuardians = $parentGuardiansModel->getByStudentId($student_id);

                // Get the student's submitted documents
                $studentDocuments = $this->studentsDocuments->getByStudentId($student_id);

                // Get all document types for the checklist
                $documentTypes = $this->documentTypes->index();

                return [
                    'student' => $student,
                    'academic_history' => $academicHistory ?: [],

                    'parent_guardians' => $parentGuardians ?: [],
                    'student_documents' => $studentDocuments ?: [],
                    'document_types' => $documentTypes ?: []
                ];
            } catch (Exception $e) {
                ErrorHandler::log($e, 'StudentsController::getStudentProfile');
                return [
                    'student' => null,
                    'academic_history' => [],
                    'parent_guardians' => [],
                    'student_documents' => [],
                    'document_types' => []
                ];
            }
        }
    }

    //=================================== boostrap the controller ====================================//
    try{
        $controller = new StudentsController($con);
        $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $gradeLevelFilter = $_GET['grade_level'] ?? '';
        $sectionFilter = $_GET['section_id'] ?? '';
        $archivedFilter = $_GET['archived'] ?? '0';
        if(!in_array($archivedFilter, ['0', '1', 'all'], true)){
            $archivedFilter = '0';
        }
        $genderFilter = $_GET['gender'] ?? '';
        if(!in_array($genderFilter, ['Male', 'Female'], true)){
            $genderFilter = '';
        }
        $filterOptions = $controller->getFilterOptions();
        $paginationData = $controller->index($currentPage, $gradeLevelFilter, $sectionFilter, $archivedFilter, $genderFilter);
        $students = $paginationData['students'];
        $pagination = [
            'currentPage' => $paginationData['currentPage'],
            'totalPages' => $paginationData['totalPages'],
            'totalRecords' => $paginationData['totalRecords'],
            'itemsPerPage' => $paginationData['itemsPerPage'],
            'hasNextPage' => $paginationData['hasNextPage'],
            'hasPrevPage' => $paginationData['hasPrevPage']
        ];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_POST['enroll_student']) || isset($_POST['edit_student']) || isset($_POST['delete_student']) || isset($_POST['archive_students']) || isset($_POST['unarchive_students'])){
                Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/student-records.php');
            }

            if(isset($_POST['enroll_student'])){
                $controller->create(
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
                    ]
                );
            }

            if(isset($_POST['edit_student'])){
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
                    $currentPage
                );
            }

            if(isset($_POST['delete_student'])){
                $studentId = $_POST['student_id'];
                $controller->delete($studentId, $currentPage);

            }

            if(isset($_POST['archive_students'])){
                $controller->archiveStudents($_POST['student_ids'] ?? [], true, $currentPage);
            }

            if(isset($_POST['unarchive_students'])){
                $controller->archiveStudents($_POST['student_ids'] ?? [], false, $currentPage);
            }
        }
    }catch(Exception $e){
        ErrorHandler::log($e, 'StudentsController (bootstrap)');
        exit();
    }

    //=================================== Handle AJAX requests ====================================//
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
        $controller = new StudentsController($con);
        header('Content-Type: application/json');

        if($_POST['action'] === 'get_student_profile'){
            $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
            $profile = $controller->getStudentProfile($student_id);
            echo json_encode($profile);
            exit();
        }
    }
?>