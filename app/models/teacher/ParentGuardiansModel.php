<?php
require_once __DIR__ . '/../Model.php';

    class TeacherParentGuardiansModel extends Model{
        protected $parents_guardians = 'parents_guardians';
        protected $students = 'students';
        protected $academic_history = 'academic_history';
        protected $sections = 'sections';

        /**
         * Get paginated parent/guardian records for students assigned to this teacher
         * @param int $teacher_id
         * @param int $offset
         * @param int $limit
         * @return array
         */
        public function getWithPagination($teacher_id, $offset, $limit){
            try{
                $query = "SELECT DISTINCT pg.*, s.first_name AS student_first_name, s.last_name AS student_last_name
                    FROM {$this->parents_guardians} pg
                    JOIN {$this->students} s ON pg.student_id = s.id
                    JOIN {$this->academic_history} ah ON ah.student_id = s.id
                    JOIN {$this->sections} ss ON ah.section_id = ss.id
                    WHERE ss.adviser_id = ?
                    ORDER BY s.last_name ASC, s.first_name ASC
                    LIMIT ? OFFSET ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('iii', $teacher_id, $limit, $offset);
                $stmt->execute();
                return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return [];
            }
        }

        /**
         * Get total count of parent/guardian records for students assigned to this teacher
         * @param int $teacher_id
         * @return int
         */
        public function getTotalCount($teacher_id){
            try{
                $query = "SELECT COUNT(DISTINCT pg.id) as total
                    FROM {$this->parents_guardians} pg
                    JOIN {$this->academic_history} ah ON ah.student_id = pg.student_id
                    JOIN {$this->sections} ss ON ah.section_id = ss.id
                    WHERE ss.adviser_id = ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $teacher_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                return (int)($row['total'] ?? 0);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return 0;
            }
        }

        /**
         * Search parent/guardian records for this teacher's students by student or guardian name
         * @param int $teacher_id
         * @param string $keyword
         * @return array
         */
        public function searchGuardians($teacher_id, $keyword){
            $keyword = trim($keyword);
            if(empty($keyword) || strlen($keyword) < 2){
                return [];
            }

            try{
                $searchKeyword = '%' . $keyword . '%';
                $query = "SELECT DISTINCT pg.*, s.first_name AS student_first_name, s.last_name AS student_last_name
                    FROM {$this->parents_guardians} pg
                    JOIN {$this->students} s ON pg.student_id = s.id
                    JOIN {$this->academic_history} ah ON ah.student_id = s.id
                    JOIN {$this->sections} ss ON ah.section_id = ss.id
                    WHERE ss.adviser_id = ?
                    AND (
                        s.first_name LIKE ? OR
                        s.last_name LIKE ? OR
                        pg.guardian_name LIKE ? OR
                        pg.father_name LIKE ? OR
                        pg.mother_name LIKE ?
                    )
                    ORDER BY s.last_name ASC, s.first_name ASC
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('isssss', $teacher_id, $searchKeyword, $searchKeyword, $searchKeyword, $searchKeyword, $searchKeyword);
                $stmt->execute();
                return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return [];
            }
        }

        /**
         * Search this teacher's students who do not yet have a guardian record
         * @param int $teacher_id
         * @param string $keyword
         * @return array
         */
        public function searchAvailableStudents($teacher_id, $keyword){
            $keyword = trim($keyword);
            if(empty($keyword) || strlen($keyword) < 2){
                return [];
            }

            try{
                $searchKeyword = '%' . $keyword . '%';
                $query = "SELECT DISTINCT s.id, s.lrn, s.first_name, s.middle_name, s.last_name
                    FROM {$this->students} s
                    JOIN {$this->academic_history} ah ON ah.student_id = s.id
                    JOIN {$this->sections} ss ON ah.section_id = ss.id
                    WHERE ss.adviser_id = ?
                    AND (
                        s.first_name LIKE ? OR
                        s.last_name LIKE ? OR
                        s.lrn LIKE ?
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM {$this->parents_guardians} pg WHERE pg.student_id = s.id
                    )
                    ORDER BY s.first_name ASC, s.last_name ASC
                    LIMIT 10
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('isss', $teacher_id, $searchKeyword, $searchKeyword, $searchKeyword);
                $stmt->execute();
                return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return [];
            }
        }

        /**
         * Check whether a student already has a guardian record
         * @param int $student_id
         * @return bool
         */
        public function studentHasGuardian($student_id){
            try{
                $query = "SELECT COUNT(*) as total FROM {$this->parents_guardians} WHERE student_id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $student_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                return ($row['total'] ?? 0) > 0;
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }
    }
