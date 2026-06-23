<?php
require_once __DIR__ . '/../Model.php';

    class DashboardModel extends Model{
        protected $school_year = 'school_year';
        protected $sections = 'sections';
        protected $academic_history = 'academic_history';

        public function __construct($con)
        {
            parent::__construct($con);
        }

        public function getActiveSchoolYear(){
            try {
                $query = "SELECT * FROM {$this->school_year} WHERE status = 'active' LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                return $stmt->get_result()->fetch_assoc();
            } catch (Exception $e) {
                error_log("Get active school year error: " . $e->getMessage());
                return null;
            }
        }

        /**
         * Sections advised by the teacher within the active school year,
         * with their currently enrolled student count.
         */
        public function getTeacherSections(int $teacherId): array{
            try {
                $query = "SELECT sec.id,
                            sec.section_name,
                            sec.grade_level,
                            sec.max_students,
                            COUNT(ah.id) AS enrolled_count
                        FROM {$this->sections} sec
                        INNER JOIN {$this->school_year} sy
                            ON sy.id = sec.school_year_id AND sy.status = 'active'
                        LEFT JOIN {$this->academic_history} ah
                            ON ah.section_id = sec.id AND ah.enrollment_status = 'Enrolled'
                        WHERE sec.adviser_id = ?
                        GROUP BY sec.id, sec.section_name, sec.grade_level, sec.max_students
                        ORDER BY sec.section_name ASC";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $teacherId);
                $stmt->execute();
                return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            } catch (Exception $e) {
                error_log("Get teacher sections error: " . $e->getMessage());
                return [];
            }
        }
    }
