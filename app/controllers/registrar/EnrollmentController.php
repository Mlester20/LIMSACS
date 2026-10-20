<?php
session_start();

require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../core/errorHandler.php';
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
                ErrorHandler::log($e, 'EnrollmentController::searchStudent');
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
                ErrorHandler::log($e, 'EnrollmentController::getStudentWithHistory');
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
         * @return array|null the currently active school year, or null if none
         */
        public function getActiveSchoolYear() {
            return $this->sectionsModel->getActiveSchoolYear();
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
         * All sections (any school year), for the Section filter dropdown.
         * @return array
         */
        public function getAllSections() {
            return $this->sectionsModel->index();
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
         * Undo a wrongly-made enrollment (wrong school year or section) by
         * permanently deleting the academic_history row. Only allowed while
         * the enrollment is still 'Enrolled' — see EnrollmentService::revertEnrollment().
         * @param int $enrollment_id
         * @return array ['success' => bool, 'message' => string]
         */
        public function revertEnrollment($enrollment_id) {
            $result = $this->enrollmentService->revertEnrollment($enrollment_id);

            if ($result['success']) {
                $this->auditLogs->log(
                    $_SESSION['id'],
                    $_SESSION['role'],
                    'REVERT ENROLLMENT',
                    'ENROLLMENT',
                    $enrollment_id,
                    'academic_history',
                    "Reverted enrollment for {$result['studentName']}"
                );
            }

            return $result;
        }

        /**
         * Correct a wrongly-made enrollment's school year/grade level/section
         * in place. See EnrollmentService::editEnrollment().
         * @param int $enrollment_id
         * @param int $school_year_id
         * @param string $grade_level
         * @param int $section_id
         * @return array ['success' => bool, 'message' => string]
         */
        public function editEnrollment($enrollment_id, $school_year_id, $grade_level, $section_id) {
            $result = $this->enrollmentService->editEnrollment($enrollment_id, $school_year_id, $grade_level, $section_id);

            if ($result['success']) {
                $this->auditLogs->log(
                    $_SESSION['id'],
                    $_SESSION['role'],
                    'EDIT ENROLLMENT',
                    'ENROLLMENT',
                    $enrollment_id,
                    'academic_history',
                    "Edited enrollment for {$result['studentName']}"
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
        public function getEnrolledStudentsWithPagination($page = 1, $itemsPerPage = 10, $status = 'Enrolled', $gradeLevel = '', $gender = '', $sectionId = '') {
            return $this->enrollmentService->getEnrolledStudentsWithPagination($page, $itemsPerPage, $status, $gradeLevel, $gender, $sectionId);
        }

        /**
         * Search enrolled students by name or LRN with pagination
         * @param string $keyword
         * @param int $page
         * @param int $itemsPerPage
         * @param string $status Enrollment status to filter by, or 'All' for every status
         * @param string $gradeLevel Grade level to filter by, or '' for every grade level
         * @param string $gender 'Male' | 'Female', or '' for all
         * @param string|int $sectionId Exact section_id match, or '' for all
         * @return array ['enrollments' => array, 'pagination' => array]
         */
        public function searchEnrolledStudents($keyword, $page = 1, $itemsPerPage = 10, $status = 'Enrolled', $gradeLevel = '', $gender = '', $sectionId = '') {
            return $this->enrollmentService->searchEnrolledStudents($keyword, $page, $itemsPerPage, $status, $gradeLevel, $gender, $sectionId);
        }

        /**
         * Get currently enrolled/transferred students across all sections, capped to $limit
         * @param int $limit
         * @return array
         */
        public function getAllEnrolledStudents($limit = 100) {
            return $this->academicHistoryModel->getAllEnrolled($limit);
        }

        /**
         * All matching enrollments grouped by section, for the printable
         * per-section report.
         * @param string $status
         * @param string $gradeLevel
         * @param string $gender
         * @param string|int $sectionId
         * @return array<string, array{section_name:string, grade_level:string, adviser_name:?string, school_year:?string, students:array}>
         */
        public function getEnrolledGroupedBySection($status = 'Enrolled', $gradeLevel = '', $gender = '', $sectionId = '') {
            $records = $this->enrollmentService->getEnrolledGroupedBySection($status, $gradeLevel, $gender, $sectionId);

            $groups = [];
            foreach ($records as $row) {
                $key = $row['section_name'] ?? 'Unassigned';
                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'section_name' => $row['section_name'] ?? 'Unassigned',
                        'grade_level'  => $row['grade_level'],
                        'adviser_name' => $row['adviser_name'],
                        'school_year'  => $row['school_year'],
                        'students'     => [],
                    ];
                }
                $groups[$key]['students'][] = $row;
            }

            return $groups;
        }

        /**
         * Candidate source sections for bulk grade-level promotion: any
         * section NOT in the currently active school year (it's a prior
         * year's section whose students are ready to move up), and not
         * already at the terminal grade level (Grade 6 students graduate
         * through the existing "Mark as Graduated" flow instead).
         * @return array each row gets a 'next_grade_level' key attached
         */
        public function getPromotableSections() {
            $activeSy = $this->sectionsModel->getActiveSchoolYear();
            $activeSyId = $activeSy['id'] ?? null;

            $allSections = $this->sectionsModel->index();

            $candidates = array_values(array_filter($allSections, function ($section) use ($activeSyId) {
                return (int) $section['school_year_id'] !== (int) $activeSyId;
            }));

            foreach ($candidates as &$section) {
                $section['next_grade_level'] = EnrollmentService::nextGradeLevel($section['grade_level']);
            }
            unset($section);

            // A section with no next grade level (Grade 6) isn't promotable.
            return array_values(array_filter($candidates, function ($section) {
                return $section['next_grade_level'] !== null;
            }));
        }

        /**
         * Students currently Enrolled in a section, for the bulk-promotion checklist.
         * @param int $section_id
         * @param int $school_year_id
         * @return array
         */
        public function getEnrolledInSection($section_id, $school_year_id) {
            return $this->academicHistoryModel->getEnrolledBySection($section_id, $school_year_id);
        }

        /**
         * Bulk-promote the given students into a target section. $source_section_id
         * is required for validation only, not to look up the students -- it
         * confirms the target section is actually the next grade level up from
         * the source, so a tampered/stale request can't promote students into
         * an unrelated grade level even though the dropdowns are already
         * filtered client-side to prevent that in normal use.
         * @param int[] $studentIds
         * @param int $source_section_id
         * @param int $target_section_id
         * @return array ['success' => bool, 'message' => string, 'promoted' => int, 'skipped' => array]
         */
        public function promoteStudents($studentIds, $source_section_id, $target_section_id) {
            $sourceSection = $this->sectionsModel->getById($source_section_id);
            if (!$sourceSection) {
                return ['success' => false, 'message' => 'Source section not found.', 'promoted' => 0, 'skipped' => []];
            }

            $targetSection = $this->sectionsModel->getById($target_section_id);
            if (!$targetSection) {
                return ['success' => false, 'message' => 'Target section not found.', 'promoted' => 0, 'skipped' => []];
            }

            $expectedGradeLevel = EnrollmentService::nextGradeLevel($sourceSection['grade_level']);
            if ($expectedGradeLevel === null || $targetSection['grade_level'] !== $expectedGradeLevel) {
                return [
                    'success' => false,
                    'message' => "Target section's grade level ({$targetSection['grade_level']}) doesn't match the expected next grade level ({$expectedGradeLevel}) for {$sourceSection['grade_level']}.",
                    'promoted' => 0,
                    'skipped' => [],
                ];
            }

            if ((int) $targetSection['school_year_id'] === (int) $sourceSection['school_year_id']) {
                return ['success' => false, 'message' => 'Target section must belong to a different (newer) school year than the source section.', 'promoted' => 0, 'skipped' => []];
            }

            // Only promote student IDs that are actually enrolled in the claimed
            // source section -- ignores anything else a tampered request might
            // have included, rather than trusting the posted ID list outright.
            $actuallyEnrolled = $this->academicHistoryModel->getEnrolledBySection($source_section_id, $sourceSection['school_year_id']);
            $validStudentIds = array_column($actuallyEnrolled, 'student_id');
            $studentIds = array_values(array_intersect(array_map('intval', $studentIds), $validStudentIds));

            if (empty($studentIds)) {
                return ['success' => false, 'message' => 'None of the selected students are actually enrolled in the source section.', 'promoted' => 0, 'skipped' => []];
            }

            $result = $this->enrollmentService->promoteStudents(
                $studentIds,
                $targetSection['school_year_id'],
                $targetSection['grade_level'],
                $target_section_id,
                $_SESSION['id'] ?? null
            );

            $promotedCount = count($result['promoted']);

            if ($promotedCount > 0) {
                $this->auditLogs->log(
                    $_SESSION['id'],
                    $_SESSION['role'],
                    'PROMOTE STUDENTS',
                    'ENROLLMENT',
                    null,
                    'academic_history',
                    "Promoted {$promotedCount} student(s) to {$targetSection['grade_level']} - {$targetSection['section_name']}"
                );
            }

            $message = "{$promotedCount} student(s) promoted.";
            if (!empty($result['skipped'])) {
                $message .= ' ' . count($result['skipped']) . ' skipped (already enrolled or section full).';
            }

            return [
                'success' => $promotedCount > 0,
                'message' => $message,
                'promoted' => $promotedCount,
                'skipped' => $result['skipped'],
            ];
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
                Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/enrollment.php');

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
                header("Location: " . BASE_URL . "/resources/views/registrar/enrollment.php");
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
                $gradeLevel   = $_POST['grade_level'] ?? '';
                $gender       = in_array($_POST['gender'] ?? '', ['Male', 'Female'], true) ? $_POST['gender'] : '';
                $sectionId    = $_POST['section_id'] ?? '';

                $allowedStatusFilters = ['Enrolled', 'Transferred', 'Dropped', 'Graduated', 'All'];
                if (!in_array($status, $allowedStatusFilters, true)) {
                    $status = 'Enrolled';
                }

                $result = $controller->searchEnrolledStudents($keyword, $page, $itemsPerPage, $status, $gradeLevel, $gender, $sectionId);
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

            // Candidate source sections for bulk grade-level promotion
            if (isset($_POST['get_promotable_sections'])) {
                $sections = $controller->getPromotableSections();
                header('Content-Type: application/json');
                echo json_encode($sections);
                exit();
            }

            // Students currently Enrolled in a section (promotion checklist)
            if (isset($_POST['get_enrolled_for_promotion'])) {
                $section_id = $_POST['section_id'] ?? null;
                $school_year_id = $_POST['school_year_id'] ?? null;
                if ($section_id && $school_year_id) {
                    $students = $controller->getEnrolledInSection($section_id, $school_year_id);
                    header('Content-Type: application/json');
                    echo json_encode($students);
                    exit();
                }
            }

            // Bulk-promote the checked students into a target section
            if (isset($_POST['promote_students'])) {
                header('Content-Type: application/json');

                if (!Csrf::isValid($_POST['csrf_token'] ?? null)) {
                    echo json_encode(['success' => false, 'message' => 'Your session has expired. Please refresh the page and try again.']);
                    exit();
                }

                $studentIds = $_POST['student_ids'] ?? [];
                $sourceSectionId = $_POST['source_section_id'] ?? null;
                $targetSectionId = $_POST['target_section_id'] ?? null;

                if (empty($studentIds) || !is_array($studentIds) || !$sourceSectionId || !$targetSectionId) {
                    echo json_encode(['success' => false, 'message' => 'Select at least one student and a target section.']);
                    exit();
                }

                $result = $controller->promoteStudents($studentIds, $sourceSectionId, $targetSectionId);
                echo json_encode($result);
                exit();
            }

            // Edit (correct) a wrongly-made enrollment — only while still Enrolled
            if (isset($_POST['edit_enrollment'])) {
                $enrollment_id = $_POST['enrollment_id'] ?? null;
                $school_year_id = $_POST['school_year_id'] ?? null;
                $grade_level = $_POST['grade_level'] ?? null;
                $section_id = $_POST['section_id'] ?? null;

                header('Content-Type: application/json');

                if (!Csrf::isValid($_POST['csrf_token'] ?? null)) {
                    echo json_encode(['success' => false, 'message' => 'Your session has expired. Please refresh the page and try again.']);
                    exit();
                }

                if (!$enrollment_id || !$school_year_id || !$grade_level || !$section_id) {
                    echo json_encode(['success' => false, 'message' => 'Missing required enrollment data.']);
                    exit();
                }

                $result = $controller->editEnrollment($enrollment_id, $school_year_id, $grade_level, $section_id);
                echo json_encode($result);
                exit();
            }
        }
    } catch (Exception $e) {
        ErrorHandler::log($e, 'EnrollmentController (bootstrap)');
        exit();
    }
?>