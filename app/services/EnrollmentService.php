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

    // Same ordering as SectionsController::GRADE_LEVELS -- duplicated here
    // (rather than requiring that controller from this service) so
    // nextGradeLevel() can compute where a bulk-promoted section goes next.
    const GRADE_LEVELS = ['Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];

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
     * The grade level right after $gradeLevel, or null if it's already the
     * last one (Grade 6 -- promoted past this point means Graduated, a
     * separate flow from grade-level promotion).
     * @param string $gradeLevel
     * @return string|null
     */
    public static function nextGradeLevel($gradeLevel) {
        $index = array_search($gradeLevel, self::GRADE_LEVELS, true);
        if ($index === false || $index + 1 >= count(self::GRADE_LEVELS)) {
            return null;
        }
        return self::GRADE_LEVELS[$index + 1];
    }

    /**
     * Bulk-promote a list of students into a target section (typically the
     * next grade level, in the newly active school year). Reuses
     * enrollStudent() per student, so each one individually gets the same
     * duplicate-enrollment and section-capacity checks a single enrollment
     * would -- a student already enrolled for that school year, or a section
     * that fills up partway through the batch, is skipped rather than
     * breaking the whole batch.
     * @param int[] $studentIds
     * @param int $school_year_id
     * @param string $grade_level
     * @param int $section_id
     * @param int|null $enrolled_by
     * @return array{promoted: int[], skipped: array} skipped entries: ['student_id'=>int, 'reason'=>string]
     */
    public function promoteStudents($studentIds, $school_year_id, $grade_level, $section_id, $enrolled_by) {
        $promoted = [];
        $skipped = [];

        foreach ($studentIds as $studentId) {
            $result = $this->enrollStudent($studentId, $school_year_id, $grade_level, $section_id, $enrolled_by);
            if ($result['success']) {
                $promoted[] = (int) $studentId;
            } else {
                $skipped[] = ['student_id' => (int) $studentId, 'reason' => $result['message']];
            }
        }

        return ['promoted' => $promoted, 'skipped' => $skipped];
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
     * Permanently delete an enrollment record. Only allowed while the enrollment
     * is still 'Enrolled' — i.e. undoing a fresh mistake (wrong school year or
     * wrong section picked at enroll time) before any status change happens —
     * so this never needs to account for cascading away a `graduates` row.
     * @param int $enrollment_id academic_history.id of the enrollment record
     * @return array ['success' => bool, 'message' => string, 'studentName' => string|null]
     */
    public function revertEnrollment($enrollment_id) {
        try {
            $enrollment = $this->academicHistoryModel->getById($enrollment_id);
            if (!$enrollment) {
                return ['success' => false, 'message' => 'Enrollment record not found.'];
            }

            $studentName = trim($enrollment['first_name'] . ' ' . $enrollment['last_name']);

            if ($enrollment['enrollment_status'] !== 'Enrolled') {
                return ['success' => false, 'message' => 'Only an enrollment that is still Enrolled can be reverted.'];
            }

            if (!$this->academicHistoryModel->delete($enrollment_id)) {
                return ['success' => false, 'message' => 'Failed to revert enrollment.'];
            }

            return [
                'success' => true,
                'message' => "Enrollment reverted for {$studentName}.",
                'studentName' => $studentName
            ];
        } catch (Exception $e) {
            error_log("Revert enrollment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while reverting the enrollment.'];
        }
    }

    /**
     * Correct a wrongly-made enrollment's school year/grade level/section in
     * place, instead of deleting it and re-enrolling the student from scratch.
     * Only allowed while the enrollment is still 'Enrolled', same scope as
     * revertEnrollment() above.
     * @param int $enrollment_id academic_history.id of the enrollment record
     * @param int $school_year_id
     * @param string $grade_level
     * @param int $section_id
     * @return array ['success' => bool, 'message' => string, 'studentName' => string|null]
     */
    public function editEnrollment($enrollment_id, $school_year_id, $grade_level, $section_id) {
        try {
            $enrollment = $this->academicHistoryModel->getById($enrollment_id);
            if (!$enrollment) {
                return ['success' => false, 'message' => 'Enrollment record not found.'];
            }

            $studentName = trim($enrollment['first_name'] . ' ' . $enrollment['last_name']);

            if ($enrollment['enrollment_status'] !== 'Enrolled') {
                return ['success' => false, 'message' => 'Only an enrollment that is still Enrolled can be edited.'];
            }

            // Only check for a duplicate enrollment if the school year is actually
            // changing — the current row IS the one on record for its own year.
            if ((int) $school_year_id !== (int) $enrollment['school_year_id']
                && $this->academicHistoryModel->isAlreadyEnrolled($enrollment['student_id'], $school_year_id)) {
                return ['success' => false, 'message' => "{$studentName} already has an enrollment record for that school year."];
            }

            if (!$this->academicHistoryModel->updateEnrollmentDetails($enrollment_id, $school_year_id, $grade_level, $section_id)) {
                return ['success' => false, 'message' => 'Failed to update enrollment.'];
            }

            return [
                'success' => true,
                'message' => "Enrollment updated for {$studentName}.",
                'studentName' => $studentName
            ];
        } catch (Exception $e) {
            error_log("Edit enrollment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the enrollment.'];
        }
    }

    /**
     * Get enrolled students with pagination
     * @param int $page Current page number (default: 1)
     * @param int $itemsPerPage Items per page (default: 10)
     * @param string $status Enrollment status to filter by, or 'All' for every status
     * @param string $gradeLevel Exact grade_level match, or '' for all
     * @param string $gender 'Male' | 'Female', or '' for all
     * @param string|int $sectionId Exact section_id match, or '' for all
     * @return array ['enrollments' => array, 'pagination' => array]
     */
    public function getEnrolledStudentsWithPagination($page = 1, $itemsPerPage = 10, $status = 'Enrolled', $gradeLevel = '', $gender = '', $sectionId = '') {
        try {
            $page = max(1, (int)$page);
            $itemsPerPage = max(1, (int)$itemsPerPage);
            $offset = ($page - 1) * $itemsPerPage;

            $conditions = [];
            $params = [];
            $types = '';
            if ($status !== 'All') {
                $conditions[] = "ah.enrollment_status = ?";
                $params[] = $status;
                $types .= 's';
            }
            if ($gradeLevel !== '') {
                $conditions[] = "ah.grade_level = ?";
                $params[] = $gradeLevel;
                $types .= 's';
            }
            if ($gender === 'Male' || $gender === 'Female') {
                $conditions[] = "s.gender = ?";
                $params[] = $gender;
                $types .= 's';
            }
            if ($sectionId !== '') {
                $conditions[] = "ah.section_id = ?";
                $params[] = $sectionId;
                $types .= 'i';
            }
            $where = $conditions ? "WHERE " . implode(' AND ', $conditions) : "";

            $query_count = "
                SELECT COUNT(*) as total
                FROM {$this->academicHistory} ah
                JOIN {$this->students} s ON ah.student_id = s.id
                {$where}
            ";
            $stmt = $this->con->prepare($query_count);
            if ($types !== '') {
                $stmt->bind_param($types, ...$params);
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
                    ah.school_year_id,
                    sy.school_year,
                    ah.grade_level,
                    ah.section_id,
                    sec.section_name,
                    ah.enrollment_status
                FROM {$this->academicHistory} ah
                JOIN {$this->students} s ON ah.student_id = s.id
                JOIN {$this->schoolYear} sy ON ah.school_year_id = sy.id
                JOIN {$this->sections} sec ON ah.section_id = sec.id
                {$where}
                ORDER BY s.last_name ASC, s.first_name ASC
                LIMIT ? OFFSET ?
            ";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param($types . 'ii', ...array_merge($params, [$itemsPerPage, $offset]));
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
     * @param string $gradeLevel Exact grade_level match, or '' for all
     * @param string $gender 'Male' | 'Female', or '' for all
     * @param string|int $sectionId Exact section_id match, or '' for all
     * @return array ['enrollments' => array, 'pagination' => array]
     */
    public function searchEnrolledStudents($keyword, $page = 1, $itemsPerPage = 10, $status = 'Enrolled', $gradeLevel = '', $gender = '', $sectionId = '') {
        try {
            $page = max(1, (int)$page);
            $itemsPerPage = max(1, (int)$itemsPerPage);
            $offset = ($page - 1) * $itemsPerPage;
            $searchKeyword = '%' . trim($keyword) . '%';

            $extraConditions = '';
            $extraParams = [];
            $extraTypes = '';
            if ($status !== 'All') {
                $extraConditions .= " AND ah.enrollment_status = ?";
                $extraParams[] = $status;
                $extraTypes .= 's';
            }
            if ($gradeLevel !== '') {
                $extraConditions .= " AND ah.grade_level = ?";
                $extraParams[] = $gradeLevel;
                $extraTypes .= 's';
            }
            if ($gender === 'Male' || $gender === 'Female') {
                $extraConditions .= " AND s.gender = ?";
                $extraParams[] = $gender;
                $extraTypes .= 's';
            }
            if ($sectionId !== '') {
                $extraConditions .= " AND ah.section_id = ?";
                $extraParams[] = $sectionId;
                $extraTypes .= 'i';
            }

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
                {$extraConditions}
            ";
            $stmt = $this->con->prepare($countQuery);
            $stmt->bind_param('sss' . $extraTypes, $searchKeyword, $searchKeyword, $searchKeyword, ...$extraParams);
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
                    ah.school_year_id,
                    sy.school_year,
                    ah.grade_level,
                    ah.section_id,
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
                {$extraConditions}
                ORDER BY s.last_name ASC, s.first_name ASC
                LIMIT ? OFFSET ?
            ";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('sss' . $extraTypes . 'ii', $searchKeyword, $searchKeyword, $searchKeyword, ...array_merge($extraParams, [$itemsPerPage, $offset]));
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

    /**
     * All matching enrollments (no pagination), ordered by grade level then
     * section, so the caller can group rows under section headers -- the
     * "per section" printable report.
     * @param string $status Enrollment status to filter by, or 'All' for every status
     * @param string $gradeLevel Exact grade_level match, or '' for all
     * @param string $gender 'Male' | 'Female', or '' for all
     * @param string|int $sectionId Exact section_id match, or '' for all
     * @return array
     */
    public function getEnrolledGroupedBySection($status = 'Enrolled', $gradeLevel = '', $gender = '', $sectionId = '') {
        try {
            $conditions = [];
            $params = [];
            $types = '';
            if ($status !== 'All') {
                $conditions[] = "ah.enrollment_status = ?";
                $params[] = $status;
                $types .= 's';
            }
            if ($gradeLevel !== '') {
                $conditions[] = "ah.grade_level = ?";
                $params[] = $gradeLevel;
                $types .= 's';
            }
            if ($gender === 'Male' || $gender === 'Female') {
                $conditions[] = "s.gender = ?";
                $params[] = $gender;
                $types .= 's';
            }
            if ($sectionId !== '') {
                $conditions[] = "ah.section_id = ?";
                $params[] = $sectionId;
                $types .= 'i';
            }
            $where = $conditions ? "WHERE " . implode(' AND ', $conditions) : "";

            $query = "
                SELECT
                    ah.id as enrollment_id,
                    s.id as student_id,
                    s.first_name,
                    s.last_name,
                    s.lrn,
                    s.gender,
                    ah.school_year_id,
                    sy.school_year,
                    ah.grade_level,
                    ah.section_id,
                    sec.section_name,
                    adv.full_name as adviser_name,
                    ah.enrollment_status
                FROM {$this->academicHistory} ah
                JOIN {$this->students} s ON ah.student_id = s.id
                JOIN {$this->schoolYear} sy ON ah.school_year_id = sy.id
                JOIN {$this->sections} sec ON ah.section_id = sec.id
                LEFT JOIN users adv ON sec.adviser_id = adv.id
                {$where}
                ORDER BY ah.grade_level ASC, sec.section_name ASC, s.last_name ASC, s.first_name ASC
            ";
            $stmt = $this->con->prepare($query);
            if ($types !== '') {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get enrolled grouped by section error: " . $e->getMessage());
            return [];
        }
    }
}
?>