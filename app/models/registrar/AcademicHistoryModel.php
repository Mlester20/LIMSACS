<?php
require_once __DIR__ . '/../Model.php';

class AcademicHistoryModel extends Model {
    private $academic_history = 'academic_history';
    private $students = 'students';
    private $sections = 'sections';
    private $school_year = 'school_year';

    /**
     * Create a new enrollment record
     * @param array $data Enrollment data
     * @return bool
     */
    public function create($data) {
        try {
            $query = "INSERT INTO {$this->academic_history} 
                     (student_id, school_year_id, grade_level, section_id, enrollment_status, created_at) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->con->prepare($query);
            $stmt->bind_param(
                'iisis',
                $data['student_id'],
                $data['school_year_id'],
                $data['grade_level'],
                $data['section_id'],
                $data['enrollment_status']
            );
            
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Create enrollment error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all enrollment records for a student
     * @param int $student_id
     * @return array
     */
    public function getByStudentId($student_id) {
        try {
            $query = "SELECT 
                        ah.*,
                        sy.school_year,
                        s.section_name,
                        s.grade_level as section_grade
                     FROM {$this->academic_history} ah
                     LEFT JOIN {$this->school_year} sy ON ah.school_year_id = sy.id
                     LEFT JOIN {$this->sections} s ON ah.section_id = s.id
                     WHERE ah.student_id = ?
                     ORDER BY ah.created_at DESC";
            
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('i', $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get enrollment history error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if student is already enrolled in a school year
     * @param int $student_id
     * @param int $school_year_id
     * @return bool
     */
    public function isAlreadyEnrolled($student_id, $school_year_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM {$this->academic_history} 
                     WHERE student_id = ? AND school_year_id = ?";
            
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('ii', $student_id, $school_year_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Check enrollment error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get current enrollment count for a section
     * @param int $section_id
     * @param int $school_year_id
     * @return int
     */
    public function getSectionEnrollmentCount($section_id, $school_year_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM {$this->academic_history} 
                     WHERE section_id = ? AND school_year_id = ? 
                     AND enrollment_status IN ('Enrolled', 'Transferred')";
            
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('ii', $section_id, $school_year_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)$row['count'];
        } catch (Exception $e) {
            error_log("Get section count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all enrollments for a section
     * @param int $section_id
     * @param int $school_year_id
     * @return array
     */
    public function getBySection($section_id, $school_year_id) {
        try {
            $query = "SELECT 
                        ah.*,
                        s.first_name,
                        s.middle_name,
                        s.last_name,
                        s.gender,
                        s.age
                     FROM {$this->academic_history} ah
                     JOIN {$this->students} s ON ah.student_id = s.id
                     WHERE ah.section_id = ? AND ah.school_year_id = ?
                     ORDER BY s.last_name ASC";
            
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('ii', $section_id, $school_year_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Get section enrollments error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update enrollment status
     * @param int $id Enrollment record ID
     * @param string $status New status
     * @return bool
     */
    public function updateStatus($id, $status) {
        try {
            $query = "UPDATE {$this->academic_history} 
                     SET enrollment_status = ? 
                     WHERE id = ?";
            
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('si', $status, $id);
            
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Update enrollment status error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete enrollment record
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM {$this->academic_history} WHERE id = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('i', $id);
            
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Delete enrollment error: " . $e->getMessage());
            return false;
        }
    }
}
?>
