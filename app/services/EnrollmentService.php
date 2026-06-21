<?php
require_once __DIR__ . '/../models/Model.php';
require_once __DIR__ . '/../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../models/registrar/SectionsModel.php';
require_once __DIR__ . '/../models/registrar/AcademicHistoryModel.php';
require_once __DIR__ . '/../models/registrar/GraduatesModel.php';

class EnrollmentService extends Model {
    // This system only covers Grade 1 - Grade 6; students must reach this
    // grade level before they're eligible to be marked as Graduated.
    const TERMINAL_GRADE_LEVEL = 'Grade 6';

    protected $academicHistory = 'academic_history';
    protected $students = 'students';
    protected $schoolYear = 'school_year';
    protected $sections = 'sections';

    private $studentsModel;
    private $sectionsModel;
    private $academicHistoryModel;
    private $graduatesModel;

    public function __construct($con) {
        parent::__construct($con);
        $this->studentsModel = new StudentsModel($con);
        $this->sectionsModel = new SectionsModel($con);
        $this->academicHistoryModel = new AcademicHistoryModel($con);
        $this->graduatesModel = new GraduatesModel($con);
    }

    /**
     * Enroll a student into a section for a school year.
     * @param int $student_id
     * @param int $school_year_id
     * @param string $grade_level
     * @param int $section_id
     * @param int|null $enrolled_by
     * @return array ['success' => bool, 'message' => string]
     */
    public function enrollStudent($student_id, $school_year_id, $grade_level, $section_id, $enrolled_by) {
        try {
            if (!$this->studentsModel->getById($student_id)) {
                return ['success' => false, 'message' => 'Student not found.'];
            }

            if ($this->academicHistoryModel->isAlreadyEnrolled($student_id, $school_year_id)) {
                return ['success' => false, 'message' => 'Student is already enrolled in this school year.'];
            }

            $section = $this->sectionsModel->getById($section_id);
            if (!$section) {
                return ['success' => false, 'message' => 'Section not found.'];
            }

            $currentEnrollment = $this->academicHistoryModel->getSectionEnrollmentCount($section_id, $school_year_id);
            if ($currentEnrollment >= $section['max_students']) {
                return ['success' => false, 'message' => "Section is full. Maximum capacity: {$section['max_students']}."];
            }

            $enrollmentData = [
                'student_id' => $student_id,
                'enrolled_by' => $enrolled_by,
                'school_year_id' => $school_year_id,
                'grade_level' => $grade_level,
                'section_id' => $section_id,
                'enrollment_status' => 'Enrolled'
            ];

            if (!$this->academicHistoryModel->create($enrollmentData)) {
                return ['success' => false, 'message' => 'Failed to create enrollment record.'];
            }

            return ['success' => true, 'message' => 'Student enrolled successfully.'];
        } catch (Exception $e) {
            error_log("Enroll student error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during enrollment.'];
        }
    }

    /**
     * Mark a student's latest enrollment as Dropped, Transferred, or Graduated.
     * For Graduated, also records the graduation details in the graduates table.
     * @param int $enrollment_id academic_history.id of the enrollment record
     * @param string $new_status 'Dropped' | 'Transferred' | 'Graduated'
     * @param array|null $graduateData Required when $new_status is 'Graduated'.
     *        Keys: graduation_date, honors, remarks
     * @param int|null $recorded_by
     * @return array ['success' => bool, 'message' => string, 'studentName' => string|null]
     */
    public function updateStudentStatus($enrollment_id, $new_status, $graduateData = null, $recorded_by = null) {
        try {
            $enrollment = $this->academicHistoryModel->getById($enrollment_id);
            if (!$enrollment) {
                return ['success' => false, 'message' => 'Enrollment record not found.'];
            }

            $studentName = trim($enrollment['first_name'] . ' ' . $enrollment['last_name']);

            // Validate before mutating status, so a failed graduate insert
            // never leaves the enrollment record in an inconsistent state.
            if ($new_status === 'Graduated') {
                if ($enrollment['grade_level'] !== self::TERMINAL_GRADE_LEVEL) {
                    return ['success' => false, 'message' => 'Only students in ' . self::TERMINAL_GRADE_LEVEL . ' are eligible to graduate.'];
                }

                if ($this->graduatesModel->findByAcademicHistoryId($enrollment_id)) {
                    return ['success' => false, 'message' => 'This student has already been recorded as graduated for this enrollment.'];
                }
            }

            if (!$this->academicHistoryModel->updateStatus($enrollment_id, $new_status)) {
                return ['success' => false, 'message' => 'Failed to update enrollment status.'];
            }

            if ($new_status === 'Graduated') {
                $graduateData['student_id'] = $enrollment['student_id'];
                $graduateData['academic_history_id'] = $enrollment_id;
                $graduateData['recorded_by'] = $recorded_by;

                if (!$this->graduatesModel->create($graduateData)) {
                    return ['success' => false, 'message' => 'Failed to record graduation details.'];
                }
            }

            return [
                'success' => true,
                'message' => "Student successfully marked as {$new_status}.",
                'studentName' => $studentName
            ];
        } catch (Exception $e) {
            error_log("Update student status error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating student status.'];
        }
    }

    /**
     * Get enrolled students with pagination
     * @param int $page Current page number (default: 1)
     * @param int $itemsPerPage Items per page (default: 10)
     * @param string $status Enrollment status to filter by, or 'All' for every status
     * @return array ['enrollments' => array, 'pagination' => array]
     */
    public function getEnrolledStudentsWithPagination($page = 1, $itemsPerPage = 10, $status = 'Enrolled') {
        try {
            $page = max(1, (int)$page);
            $itemsPerPage = max(1, (int)$itemsPerPage);
            $offset = ($page - 1) * $itemsPerPage;

            $statusFilter = ($status !== 'All') ? "WHERE ah.enrollment_status = ?" : "";

            $query_count = "
                SELECT COUNT(*) as total
                FROM {$this->academicHistory} ah
                JOIN {$this->students} s ON ah.student_id = s.id
                {$statusFilter}
            ";
            $stmt = $this->con->prepare($query_count);
            if ($status !== 'All') {
                $stmt->bind_param('s', $status);
            }
            $stmt->execute();
            $totalRecords = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

            $query = "
                SELECT
                    ah.id as enrollment_id,
                    s.id as student_id,
                    s.first_name,
                    s.last_name,
                    s.lrn,
                    sy.school_year,
                    ah.grade_level,
                    sec.section_name,
                    ah.enrollment_status
                FROM {$this->academicHistory} ah
                JOIN {$this->students} s ON ah.student_id = s.id
                JOIN {$this->schoolYear} sy ON ah.school_year_id = sy.id
                JOIN {$this->sections} sec ON ah.section_id = sec.id
                {$statusFilter}
                ORDER BY s.last_name ASC, s.first_name ASC
                LIMIT ? OFFSET ?
            ";
            $stmt = $this->con->prepare($query);
            if ($status !== 'All') {
                $stmt->bind_param('sii', $status, $itemsPerPage, $offset);
            } else {
                $stmt->bind_param('ii', $itemsPerPage, $offset);
            }
            $stmt->execute();
            $enrollments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $totalPages = ceil($totalRecords / $itemsPerPage);

            return [
                'enrollments' => $enrollments,
                'pagination' => [
                    'currentPage'  => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'totalRecords' => $totalRecords,
                    'totalPages'   => $totalPages,
                    'hasPrevPage'  => $page > 1,
                    'hasNextPage'  => $page < $totalPages
                ]
            ];
        } catch (Exception $e) {
            error_log("Get enrolled students error: " . $e->getMessage());
            return [
                'enrollments' => [],
                'pagination' => [
                    'currentPage' => 1,
                    'itemsPerPage' => $itemsPerPage,
                    'totalRecords' => 0,
                    'totalPages' => 0,
                    'hasPrevPage' => false,
                    'hasNextPage' => false
                ]
            ];
        }
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
        try {
            $page = max(1, (int)$page);
            $itemsPerPage = max(1, (int)$itemsPerPage);
            $offset = ($page - 1) * $itemsPerPage;
            $searchKeyword = '%' . trim($keyword) . '%';

            $statusFilter = ($status !== 'All') ? "AND ah.enrollment_status = ?" : "";

            // Total count for pagination
            $countQuery = "
                SELECT COUNT(*) as total
                FROM {$this->academicHistory} ah
                JOIN {$this->students} s ON ah.student_id = s.id
                WHERE (
                    s.first_name LIKE ? OR
                    s.last_name LIKE ? OR
                    s.lrn LIKE ?
                )
                {$statusFilter}
            ";
            $stmt = $this->con->prepare($countQuery);
            if ($status !== 'All') {
                $stmt->bind_param('ssss', $searchKeyword, $searchKeyword, $searchKeyword, $status);
            } else {
                $stmt->bind_param('sss', $searchKeyword, $searchKeyword, $searchKeyword);
            }
            $stmt->execute();
            $totalRecords = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

            // Paginated results
            $query = "
                SELECT
                    ah.id as enrollment_id,
                    s.id as student_id,
                    s.first_name,
                    s.last_name,
                    s.lrn,
                    sy.school_year,
                    ah.grade_level,
                    sec.section_name,
                    ah.enrollment_status
                FROM {$this->academicHistory} ah
                JOIN {$this->students} s ON ah.student_id = s.id
                JOIN {$this->schoolYear} sy ON ah.school_year_id = sy.id
                JOIN {$this->sections} sec ON ah.section_id = sec.id
                WHERE (
                    s.first_name LIKE ? OR
                    s.last_name LIKE ? OR
                    s.lrn LIKE ?
                )
                {$statusFilter}
                ORDER BY s.last_name ASC, s.first_name ASC
                LIMIT ? OFFSET ?
            ";
            $stmt = $this->con->prepare($query);
            if ($status !== 'All') {
                $stmt->bind_param('ssssii', $searchKeyword, $searchKeyword, $searchKeyword, $status, $itemsPerPage, $offset);
            } else {
                $stmt->bind_param('sssii', $searchKeyword, $searchKeyword, $searchKeyword, $itemsPerPage, $offset);
            }
            $stmt->execute();
            $enrollments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $totalPages = ceil($totalRecords / $itemsPerPage);

            return [
                'enrollments' => $enrollments,
                'pagination' => [
                    'currentPage'  => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'totalRecords' => $totalRecords,
                    'totalPages'   => $totalPages,
                    'hasPrevPage'  => $page > 1,
                    'hasNextPage'  => $page < $totalPages
                ]
            ];
        } catch (Exception $e) {
            error_log("Search enrolled students error: " . $e->getMessage());
            return ['enrollments' => [], 'pagination' => []];
        }
    }
}
?>