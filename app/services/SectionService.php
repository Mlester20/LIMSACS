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

        /**
         * Map enrollment counts and capacity to sections
         * @param array $sections - Array of sections
         * @param array $total_counts - Array of enrollment counts from getTotalCount()
         * @return array - Sections with total_students and max_capacity mapped
         */
        public function mapEnrollmentToSections($sections, $total_counts){
            if(empty($sections)){
                return [];
            }

            // If no enrollment data, set defaults
            if(empty($total_counts) || !is_array($total_counts)){
                foreach($sections as $key => $section){
                    $sections[$key]['total_students'] = 0;
                    $sections[$key]['max_capacity'] = $section['max_students'] ?? 35;
                }
                return $sections;
            }

            // Create lookup maps
            $count_map = [];
            $max_map = [];
            
            foreach($total_counts as $count){
                $section_id = $count['section_id'] ?? null;
                if($section_id !== null){
                    $count_map[$section_id] = $count['total_section_students'] ?? 0;
                    $max_map[$section_id] = $count['max_students'] ?? 35;
                }
            }

            // Map counts to sections
            foreach($sections as $key => $section){
                $section_id = $section['id'] ?? null;
                if($section_id !== null){
                    $sections[$key]['total_students'] = $count_map[$section_id] ?? 0;
                    $sections[$key]['max_capacity'] = $max_map[$section_id] ?? 35;
                } else {
                    $sections[$key]['total_students'] = 0;
                    $sections[$key]['max_capacity'] = 35;
                }
            }

            return $sections;
        }
    }