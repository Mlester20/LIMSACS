<?php
session_start();

require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../models/registrar/AcademicHistoryModel.php';
require_once __DIR__ . '/../../models/registrar/SectionsModel.php';
require_once __DIR__ . '/../../services/StudentsService.php';
require_once __DIR__ . '/../../services/EnrollmentService.php';

    class EnrollmentController extends Controller {

        // Kept for backward compatibility with views that reference
        // EnrollmentController::TERMINAL_GRADE_LEVEL; EnrollmentService owns
        // the actual graduation-eligibility business rule.
        const TERMINAL_GRADE_LEVEL = EnrollmentService::TERMINAL_GRADE_LEVEL;

        private $academicHistoryModel;
        private $sectionsModel;
        protected $auditLogs;
        private $enrollmentService;

        public function __construct($con) {
            parent::__construct(new StudentsModel($con));
            $this->academicHistoryModel = new AcademicHistoryModel($con);
            $this->sectionsModel = new SectionsModel($con);
            $this->auditLogs = new AuditLogs($con);
            $this->enrollmentService = new EnrollmentService($con);
        }

        /**
         * Search for existing student by LRN or name
         * @param string $searchTerm
         * @return array
         */
        public function searchStudent($searchTerm) {
            try {
                $studentsService = new StudentsService($this->model->con);
                $results = $studentsService->searchStudents($searchTerm);
                
                // Limit to 10 results
                return array_slice($results, 0, 10);
            } catch (Exception $e) {
                error_log("Search student error: " . $e->getMessage());
                return [];
            }
        }

        /**
         * Get student details with enrollment history
         * @param int $student_id
         * @return array|null
         */
        public function getStudentWithHistory($student_id) {
            try {
                $student = $this->model->getById($student_id);

                if ($student) {
                    $student['enrollment_history'] = $this->academicHistoryModel->getByStudentId($student_id);
                }

                return $student;
            } catch (Exception $e) {
                error_log("Get student with history error: " . $e->getMessage());
                return null;
            }
        }

        /**
         * Enroll student in a section
         * @param int $student_id
         * @param int $school_year_id
         * @param string $grade_level
         * @param int $section_id
         * @return array ['success' => bool, 'message' => string]
         */
        public function enrollStudent($student_id, $school_year_id, $grade_level, $section_id) {
            $result = $this->enrollmentService->enrollStudent(
                $student_id,
                $school_year_id,
                $grade_level,
                $section_id,
                $_SESSION['id'] ?? null
            );

            if ($result['success']) {
                $this->auditLogs->log(
                    $_SESSION['id'],
                    $_SESSION['role'],
                    'ENROLL STUDENT',
                    'ENROLLMENT',
                    null,
                    'academic_history',
                    "Student enrolled in {$grade_level}"
                );
            }

            return $result;
        }

        /**
         * Get all available school years
         * @return array
         */
        public function getSchoolYears() {
            return $this->sectionsModel->getAllSchoolYears();
        }

        /**
         * Get sections by school year and grade level
         * @param int $school_year_id
         * @param string $grade_level
         * @return array
         */
        public function getSectionsByGradeLevel($school_year_id, $grade_level) {
            return $this->sectionsModel->getByGradeLevelWithEnrollment($school_year_id, $grade_level);
        }

        /**
         * Get unique grade levels
         * @return array
         */
        public function getGradeLevels() {
            return $this->sectionsModel->getDistinctGradeLevels();
        }

        /**
         * Get enrollment history for a student
         * @param int $student_id
         * @return array
         */
        public function getEnrollmentHistory($student_id) {
            return $this->academicHistoryModel->getByStudentId($student_id);
        }

        /**
         * Update enrollment status
         * @param int $enrollment_id
         * @param string $status
         * @return bool
         */
        public function updateEnrollmentStatus($enrollment_id, $status) {
            return $this->academicHistoryModel->updateStatus($enrollment_id, $status);
        }

        /**
         * Mark a student's latest enrollment as Dropped, Transferred, or Graduated.
         * For Graduated, also records the graduation details in the graduates table.
         * @param int $enrollment_id academic_history.id of the enrollment record
         * @param string $new_status 'Dropped' | 'Transferred' | 'Graduated'
         * @param array|null $graduateData Required when $new_status is 'Graduated'.
         *        Keys: graduation_date, honors, remarks
         * @return array ['success' => bool, 'message' => string]
         */
        public function updateStudentStatus($enrollment_id, $new_status, $graduateData = null) {
            $result = $this->enrollmentService->updateStudentStatus(
                $enrollment_id,
                $new_status,
                $graduateData,
                $_SESSION['id'] ?? null
            );

            if ($result['success']) {
                $actionMap = [
                    'Graduated'   => 'GRADUATE STUDENT',
                    'Dropped'     => 'DROP STUDENT',
                    'Transferred' => 'TRANSFER STUDENT'
                ];

                $this->auditLogs->log(
                    $_SESSION['id'],
                    $_SESSION['role'],
                    $actionMap[$new_status] ?? strtoupper($new_status) . ' STUDENT',
                    'ENROLLMENT',
                    $enrollment_id,
                    'academic_history',
                    "Marked {$result['studentName']} as {$new_status}"
                );
            }

            return $result;
        }

        /**
         * Abstract method implementation - not used in EnrollmentController
         */
        public function index() {
            // Enrollment list is handled by enrollment.php view
            return [];
        }

        /**
         * Abstract method implementation - not used in EnrollmentController
         * Use enrollStudent() instead
         */
        public function create($data) {
            // Use enrollStudent() for enrollment creation
            return false;
        }

        /**
         * Abstract method implementation - not used in EnrollmentController
         * Use updateEnrollmentStatus() instead
         */
        public function update($id, $data) {
            // Use updateEnrollmentStatus() for enrollment updates
            return false;
        }

        /**
         * Abstract method implementation - not used in EnrollmentController
         */
        public function delete($id) {
            // Deletion handled by AcademicHistoryModel
            return $this->academicHistoryModel->delete($id);
        }

        /**
         * Get enrolled students with pagination
         * @param int $page - Current page number (default: 1)
         * @param int $itemsPerPage - Items per page (default: 10)
         * @param string $status - Enrollment status to filter by, or 'All' for every status
         * @return array - Array with 'enrollments' and 'pagination' keys
         */
        public function getEnrolledStudentsWithPagination($page = 1, $itemsPerPage = 10, $status = 'Enrolled') {
            return $this->enrollmentService->getEnrolledStudentsWithPagination($page, $itemsPerPage, $status);
        }

        /**
         * Search enrolled students by name or LRN with pagination
         * @param string $keyword
         * @param int $page
         * @param int $itemsPerPage
         * @param string $status Enrollment status to filter by, or 'All' for every status
         * @return array ['enrollments' => array, 'pagination' => array]
         */
        public function searchEnrolledStudents($keyword, $page = 1, $itemsPerPage = 10, $status = 'Enrolled') {
            return $this->enrollmentService->searchEnrolledStudents($keyword, $page, $itemsPerPage, $status);
        }

        /**
         * Get currently enrolled/transferred students across all sections, capped to $limit
         * @param int $limit
         * @return array
         */
        public function getAllEnrolledStudents($limit = 100) {
            return $this->academicHistoryModel->getAllEnrolled($limit);
        }
    }

    // ===== Bootstrap the controller =====
    try {
        $controller = new EnrollmentController($con);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Search student
            if (isset($_POST['search_student'])) {
                $searchTerm = $_POST['search_term'] ?? '';
                $results = $controller->searchStudent($searchTerm);
                header('Content-Type: application/json');
                echo json_encode($results);
                exit();
            }

            // Get student with history
            if (isset($_POST['get_student_details'])) {
                $student_id = $_POST['student_id'] ?? null;
                if ($student_id) {
                    $student = $controller->getStudentWithHistory($student_id);
                    header('Content-Type: application/json');
                    echo json_encode($student);
                    exit();
                }
            }

            // Get sections by grade and school year
            if (isset($_POST['get_sections'])) {
                $school_year_id = $_POST['school_year_id'] ?? null;
                $grade_level = $_POST['grade_level'] ?? null;
                if ($school_year_id && $grade_level) {
                    $sections = $controller->getSectionsByGradeLevel($school_year_id, $grade_level);
                    header('Content-Type: application/json');
                    echo json_encode($sections);
                    exit();
                }
            }

            // Enroll student
            if (isset($_POST['enroll_student'])) {
                Csrf::requireValidOnPost('../../../resources/views/registrar/enrollment.php');

                $student_id = $_POST['student_id'] ?? null;
                $enrolled_by = $_SESSION['id'] ?? null;
                $school_year_id = $_POST['school_year_id'] ?? null;
                $grade_level = $_POST['grade_level'] ?? null;
                $section_id = $_POST['section_id'] ?? null;

                if ($student_id && $school_year_id && $grade_level && $section_id) {
                    $result = $controller->enrollStudent($student_id, $school_year_id, $grade_level, $section_id);
                    if ($result['success']) {
                        FlashMessage::setFlash('success', $result['message']);
                    } else {
                        FlashMessage::setFlash('error', $result['message']);
                    }
                } else {
                    FlashMessage::setFlash('error', 'Missing required enrollment data.');
                }
                header("Location: ../../../resources/views/registrar/enrollment.php");
                exit();
            }

            // Get all enrolled students
            if (isset($_POST['get_all_enrolled_students'])) {
                $enrollments = $controller->getAllEnrolledStudents(100);
                header('Content-Type: application/json');
                echo json_encode($enrollments);
                exit();
            }

            // Get enrollment history for a student
            if (isset($_POST['get_enrollment_history'])) {
                $student_id = $_POST['student_id'] ?? null;
                
                if ($student_id) {
                    $history = $controller->getEnrollmentHistory($student_id);
                    header('Content-Type: application/json');
                    echo json_encode($history);
                    exit();
                }
            }

            // Search enrolled students
            if (isset($_POST['search_enrolled'])) {
                $keyword      = $_POST['keyword'] ?? '';
                $page         = $_POST['page'] ?? 1;
                $itemsPerPage = $_POST['items_per_page'] ?? 10;
                $status       = $_POST['status'] ?? 'Enrolled';

                $allowedStatusFilters = ['Enrolled', 'Transferred', 'Dropped', 'Graduated', 'All'];
                if (!in_array($status, $allowedStatusFilters, true)) {
                    $status = 'Enrolled';
                }

                $result = $controller->searchEnrolledStudents($keyword, $page, $itemsPerPage, $status);
                header('Content-Type: application/json');
                echo json_encode($result);
                exit();
            }

            // Update student status (Dropped / Transferred / Graduated)
            if (isset($_POST['update_status'])) {
                $enrollment_id = $_POST['enrollment_id'] ?? null;
                $new_status = $_POST['new_status'] ?? null;
                $allowedStatuses = ['Dropped', 'Transferred', 'Graduated'];

                header('Content-Type: application/json');

                if (!Csrf::isValid($_POST['csrf_token'] ?? null)) {
                    echo json_encode(['success' => false, 'message' => 'Your session has expired. Please refresh the page and try again.']);
                    exit();
                }

                if (!$enrollment_id || !in_array($new_status, $allowedStatuses, true)) {
                    echo json_encode(['success' => false, 'message' => 'Missing or invalid status update data.']);
                    exit();
                }

                $graduateData = null;
                if ($new_status === 'Graduated') {
                    $graduation_date = $_POST['graduation_date'] ?? null;
                    if (!$graduation_date) {
                        echo json_encode(['success' => false, 'message' => 'Graduation date is required.']);
                        exit();
                    }

                    $graduateData = [
                        'graduation_date' => $graduation_date,
                        'honors' => !empty($_POST['honors']) ? $_POST['honors'] : null,
                        'remarks' => !empty($_POST['remarks']) ? $_POST['remarks'] : null
                    ];
                }

                $result = $controller->updateStudentStatus($enrollment_id, $new_status, $graduateData);
                echo json_encode($result);
                exit();
            }
        }
    } catch (Exception $e) {
        error_log("Enrollment controller error: " . $e->getMessage());
        exit();
    }
?>