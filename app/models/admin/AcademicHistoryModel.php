<?php
require_once __DIR__ . '/../Model.php';

    class AcademicHistoryModel extends Model{
        protected $students = 'students';
        protected $academic_history = 'academic_history';
        protected $users = 'users';
        protected $section = 'sections';
        protected $school_year = 'school_year';

        public function getPaginated($limit, $offset){
            try{
                $query = "SELECT
                        ah.*,
                        s.first_name AS student_first_name,
                        s.last_name AS student_last_name,
                        u.full_name AS enrolled_by_registrar_name,
                        sec.section_name AS section_name,
                        sec.grade_level AS section_grade_level,
                        sy.school_year,
                        
                        CONCAT(s.first_name, ' ', s.last_name) AS student_full_name
                        FROM {$this->academic_history} ah
                        LEFT JOIN {$this->students} s ON ah.student_id = s.id
                        LEFT JOIN {$this->users} u ON ah.enrolled_by = u.id
                        LEFT JOIN {$this->section} sec ON ah.section_id = sec.id
                        LEFT JOIN {$this->school_year} sy ON ah.school_year_id = sy.id
                        ORDER BY ah.id ASC
                        LIMIT ? OFFSET ?
                    ";
                    $stmt = $this->con->prepare($query);
                    $stmt->bind_param("ii", $limit, $offset);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }

        /**
         * Get the total number of academic history records.
         * Needed to calculate total pages for pagination.
         */
        public function getTotalCount(){
            try{
                $query = "SELECT COUNT(*) AS total FROM {$this->academic_history}";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                return (int)$row['total'];
            }catch(Exception $e){
                error_log($e->getMessage());
                return 0;
            }
        }
    }