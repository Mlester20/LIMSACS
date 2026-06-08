<?php
require_once __DIR__ . '/../models/Model.php';

    class SectionService extends Model{
        protected $sections = 'sections';
        protected $sy = 'school_year';
        protected $teachers = 'users'; //Teachers are stored in the users table with a specific role

        /**
         * Search Specific Section
         * Prepare the Like keyword
         */
        public function searchSection($keyword){
            $keyword = trim($keyword);

            if(empty($keyword) || strlen($keyword) < 2){
                return [];
            }

            $searchKeyword = '%' . $keyword . '%';
           
            $query = "
                SELECT 
                    s.id,
                    s.section_name,
                    s.grade_level,
                    s.adviser_id,
                    s.school_year_id,
                    u.full_name AS adviser_name,  
                    sy.school_year,
                    (SELECT COUNT(*) FROM student_sections ss WHERE ss.section_id = s.id) as total_students
                FROM {$this->sections} s
                LEFT JOIN {$this->teachers} u ON s.adviser_id = u.id
                LEFT JOIN {$this->sy} sy ON s.school_year_id = sy.id
                WHERE 
                    s.section_name LIKE ? 
                    OR s.grade_level LIKE ?
                    OR u.full_name LIKE ?            
                ORDER BY s.section_name ASC
            ";

            $stmt = null;
            $result = [];

            try{
                $stmt = $this->con->prepare($query);

                if(!$stmt){
                    return [];
                }

                $stmt->bind_param(
                    "sss",
                    $searchKeyword, $searchKeyword, $searchKeyword
                );
                $stmt->execute();

                $result = $stmt->get_result();
                $result = $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Search Section Error: " . $e->getMessage());
                return [];
            }finally{
                if($stmt){
                    $stmt->close();
                }
            }
            return $result ?? [];
        }
    }