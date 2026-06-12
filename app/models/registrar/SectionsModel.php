<?php
require_once __DIR__ . '/../Model.php';

    class SectionsModel extends Model{
        protected $users = 'users';
        protected $school_year = 'school_year';
        protected $sections = 'sections';
        protected $students = 'students';

        public function index(){
            $query = "SELECT
                    s.*,
                    u.full_name as adviser_name,
                    sy.school_year
                    FROM {$this->sections} s
                    LEFT JOIN {$this->users} u ON s.adviser_id = u.id
                    LEFT JOIN {$this->school_year} sy ON s.school_year_id = sy.id
                ";
            $stmt = $this->con->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function create($data){
            try {
                $query = "INSERT INTO {$this->sections} (section_name, grade_level, adviser_id, school_year_id, max_students) VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    "ssiii",
                    $data['section_name'],
                    $data['grade_level'],
                    $data['adviser_id'],
                    $data['school_year_id'],
                    $data['max_students']
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log("Create section error: " . $e->getMessage());
                return false;
            }
        }

        public function getAvailableTeachers(){
            try{
                $query = "SELECT id, full_name, email FROM {$this->users} 
                          WHERE role = 'teacher' 
                          AND id NOT IN (
                              SELECT DISTINCT adviser_id FROM {$this->sections} WHERE adviser_id IS NOT NULL
                          )";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log('Get teachers error: ' . $e->getMessage());
                return [];
            }
        }

        public function getAllTeachers(){
            try{
                $query = "SELECT id, full_name, email FROM {$this->users} WHERE role = 'teacher' ORDER BY full_name";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Get all teachers error: " . $e->getMessage());
                return [];
            }
        }



        public function getActiveSchoolYear(){
            try {
                $query = "SELECT * FROM {$this->school_year} WHERE status = 'active' LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            } catch (Exception $e) {
                error_log("Get active school year error: " . $e->getMessage());
                return null;
            }
        }

        public function update($id, $data){
            try{
                $query = "UPDATE {$this->sections} SET section_name = ?, grade_level = ?, adviser_id = ?, school_year_id = ?, max_students = ? WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    "ssiiii",
                    $data['section_name'],
                    $data['grade_level'],
                    $data['adviser_id'],
                    $data['school_year_id'],
                    $data['max_students'],
                    $id
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log("Update section error: " . $e->getMessage());
                return false;
            }
        }

        public function delete($id){
            try{
                $query = "DELETE FROM {$this->sections} WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log("Delete section error: " . $e->getMessage());
                return false;
            }
        }

    }