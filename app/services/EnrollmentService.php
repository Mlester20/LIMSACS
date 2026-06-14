<?php
require_once __DIR__ . '/../models/Model.php';

class EnrollmentService extends Model {
    protected $academicHistory = 'academic_history';
    protected $students = 'students';
    protected $schoolYear = 'school_year';
    protected $sections = 'sections';

    /**
     * Search enrolled students by name or LRN with pagination
     * @param string $keyword
     * @param int $page
     * @param int $itemsPerPage
     * @return array ['enrollments' => array, 'pagination' => array]
     */
    public function searchEnrolledStudents($keyword, $page = 1, $itemsPerPage = 10) {
        try {
            $page = max(1, (int)$page);
            $itemsPerPage = max(1, (int)$itemsPerPage);
            $offset = ($page - 1) * $itemsPerPage;
            $searchKeyword = '%' . trim($keyword) . '%';

            // Total count for pagination
            $countQuery = "
                SELECT COUNT(*) as total
                FROM {$this->academicHistory} ah
                JOIN {$this->students} s ON ah.student_id = s.id
                WHERE ah.enrollment_status = 'Enrolled'
                AND (
                    s.first_name LIKE ? OR
                    s.last_name LIKE ? OR
                    s.lrn LIKE ?
                )
            ";
            $stmt = $this->con->prepare($countQuery);
            $stmt->bind_param('sss', $searchKeyword, $searchKeyword, $searchKeyword);
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
                WHERE ah.enrollment_status = 'Enrolled'
                AND (
                    s.first_name LIKE ? OR
                    s.last_name LIKE ? OR
                    s.lrn LIKE ?
                )
                ORDER BY s.last_name ASC, s.first_name ASC
                LIMIT ? OFFSET ?
            ";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('sssii', $searchKeyword, $searchKeyword, $searchKeyword, $itemsPerPage, $offset);
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
