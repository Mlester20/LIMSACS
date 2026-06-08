<?php
session_start();

require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/message.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../models/registrar/AcademicHistoryModel.php';
require_once __DIR__ . '/../../models/registrar/SectionsModel.php';
require_once __DIR__ . '/../../services/StudentsService.php';

class EnrollmentController extends Controller {

    private $academicHistoryModel;
    private $sectionsModel;
    protected $auditLogs;

    public function __construct($con) {
        parent::__construct(new StudentsModel($con));
        $this->academicHistoryModel = new AcademicHistoryModel($con);
        $this->sectionsModel = new SectionsModel($con);
        $this->auditLogs = new AuditLogs($con);
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
            $query = "SELECT * FROM students WHERE id = ?";
            $stmt = $this->model->con->prepare($query);
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();

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
        try {
            // Validate student exists
            $query = "SELECT id FROM students WHERE id = ?";
            $stmt = $this->model->con->prepare($query);
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) {
                return ['success' => false, 'message' => 'Student not found.'];
            }

            // Check if already enrolled in this school year
            if ($this->academicHistoryModel->isAlreadyEnrolled($student_id, $school_year_id)) {
                return ['success' => false, 'message' => 'Student is already enrolled in this school year.'];
            }

            // Get section details
            $query = "SELECT max_students FROM sections WHERE id = ?";
            $stmt = $this->model->con->prepare($query);
            $stmt->bind_param('i', $section_id);
            $stmt->execute();
            $sectionResult = $stmt->get_result()->fetch_assoc();

            if (!$sectionResult) {
                return ['success' => false, 'message' => 'Section not found.'];
            }

            // Check section capacity
            $currentEnrollment = $this->academicHistoryModel->getSectionEnrollmentCount($section_id, $school_year_id);
            if ($currentEnrollment >= $sectionResult['max_students']) {
                return ['success' => false, 'message' => "Section is full. Maximum capacity: {$sectionResult['max_students']}."];
            }

            // Create enrollment record
            $enrollmentData = [
                'student_id' => $student_id,
                'school_year_id' => $school_year_id,
                'grade_level' => $grade_level,
                'section_id' => $section_id,
                'enrollment_status' => 'Enrolled'
            ];

            if ($this->academicHistoryModel->create($enrollmentData)) {
                // Log the action
                $this->auditLogs->log(
                    $_SESSION['id'],
                    $_SESSION['role'],
                    'ENROLL STUDENT',
                    'ENROLLMENT',
                    null,
                    'academic_history',
                    "Student enrolled in {$grade_level}"
                );

                return ['success' => true, 'message' => 'Student enrolled successfully.'];
            } else {
                return ['success' => false, 'message' => 'Failed to create enrollment record.'];
            }
        } catch (Exception $e) {
            error_log("Enroll student error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during enrollment.'];
        }
    }

    /**
     * Get all available school years
     * @return array
     */
    public function getSchoolYears() {
        try {
            $query = "SELECT id, school_year, status FROM school_year ORDER BY school_year DESC";
            $stmt = $this->model->con->prepare($query);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get school years error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get sections by school year and grade level
     * @param int $school_year_id
     * @param string $grade_level
     * @return array
     */
    public function getSectionsByGradeLevel($school_year_id, $grade_level) {
        try {
            $query = "SELECT s.*, COUNT(ah.id) as current_enrollment
                     FROM sections s
                     LEFT JOIN academic_history ah 
                        ON s.id = ah.section_id 
                        AND ah.school_year_id = ?
                        AND ah.enrollment_status IN ('Enrolled', 'Transferred')
                     WHERE s.school_year_id = ? AND s.grade_level = ?
                     GROUP BY s.id
                     ORDER BY s.section_name ASC";
            
            $stmt = $this->model->con->prepare($query);
            $stmt->bind_param('iis', $school_year_id, $school_year_id, $grade_level);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get sections by grade error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unique grade levels
     * @return array
     */
    public function getGradeLevels() {
        try {
            $query = "SELECT DISTINCT grade_level FROM sections WHERE grade_level IS NOT NULL ORDER BY grade_level ASC";
            $stmt = $this->model->con->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $grades = [];
            while ($row = $result->fetch_assoc()) {
                $grades[] = $row['grade_level'];
            }
            return $grades;
        } catch (Exception $e) {
            error_log("Get grade levels error: " . $e->getMessage());
            return [];
        }
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
            $student_id = $_POST['student_id'] ?? null;
            $school_year_id = $_POST['school_year_id'] ?? null;
            $grade_level = $_POST['grade_level'] ?? null;
            $section_id = $_POST['section_id'] ?? null;

            if ($student_id && $school_year_id && $grade_level && $section_id) {
                $result = $controller->enrollStudent($student_id, $school_year_id, $grade_level, $section_id);
                if ($result['success']) {
                    setFlash('success', $result['message']);
                } else {
                    setFlash('error', $result['message']);
                }
            } else {
                setFlash('error', 'Missing required enrollment data.');
            }
            header("Location: ../../../resources/views/registrar/enrollment.php");
            exit();
        }

        // Get all enrolled students
        if (isset($_POST['get_all_enrolled_students'])) {
            $query = "SELECT 
                        ah.id, 
                        ah.student_id, 
                        ah.enrollment_status,
                        ah.grade_level, 
                        ah.created_at,
                        s.lrn,
                        s.first_name,
                        s.last_name,
                        sec.section_name,
                        sy.school_year
                     FROM academic_history ah
                     JOIN students s ON ah.student_id = s.id
                     JOIN sections sec ON ah.section_id = sec.id
                     JOIN school_year sy ON ah.school_year_id = sy.id
                     WHERE ah.enrollment_status IN ('Enrolled', 'Transferred')
                     ORDER BY sy.school_year DESC, s.first_name ASC
                     LIMIT 100";
            
            try {
                $stmt = $con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                $enrollments = $result->fetch_all(MYSQLI_ASSOC);
                
                header('Content-Type: application/json');
                echo json_encode($enrollments);
                exit();
            } catch (Exception $e) {
                error_log("Get enrolled students error: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode([]);
                exit();
            }
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
    }
} catch (Exception $e) {
    error_log("Enrollment controller error: " . $e->getMessage());
    exit();
}
?>
