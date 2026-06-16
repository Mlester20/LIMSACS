<?php
require_once __DIR__ . '/../Model.php';

    class StudentsModel extends Model{
        private $students = 'students';

        public function index(){
            try{
                $query = "SELECT * FROM {$this->students} ORDER BY last_name ASC";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();

                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                return $e->getMessage();
            }
        }

        /**
         * Get paginated students
         * @param int $limit Records per page
         * @param int $offset Starting position
         * @return array Paginated student records
         */
        public function getPaginated($limit = 10, $offset = 0){
            try{
                $query = "SELECT * FROM {$this->students} ORDER BY last_name ASC LIMIT ? OFFSET ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('ii', $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();

                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                return $e->getMessage();
            }
        }

        /**
         * Get total count of students
         * @return int Total number of students
         */
        public function getTotalCount(){
            try{
                $query = "SELECT COUNT(*) as total FROM {$this->students} ORDER by last_name ASC";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                return $row['total'];
            }catch(Exception $e){
                error_log($e->getMessage());
                return 0;
            }
        }

        /**
         * Get student by LRN
         * @param string $lrn
         * @return array|null
         */
        public function getByLrn($lrn){
            try{
                $query = "SELECT * FROM {$this->students} WHERE lrn = ? LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('s', $lrn);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }catch(Exception $e){
                error_log($e->getMessage());
                return null;
            }
        }

        /**
         * Check if LRN exists
         * @param string $lrn
         * @return bool  
         */       
        public function lrnExists($lrn){
            try{
                $query = "SELECT id FROM {$this->students} WHERE lrn = ? LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('s', $lrn);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->num_rows > 0;
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        } 

        /**
         * Create new student record
         * @param array $data Student data
         */
        public function create($data){
            try{
                $query = "INSERT INTO {$this->students}(lrn, first_name, middle_name, last_name, suffix, gender, birth_date, age, place_of_birth, nationality, religion, address, contact_number) 
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'sssssssisssss',
                    $data['lrn'],
                    $data['first_name'],
                    $data['middle_name'],
                    $data['last_name'],
                    $data['suffix'],
                    $data['gender'],
                    $data['birth_date'],
                    $data['age'],
                    $data['place_of_birth'],
                    $data['nationality'],
                    $data['religion'],
                    $data['address'],
                    $data['contact_number']
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }

        public function update($id, $data){
            try{
                $query = "UPDATE {$this->students}
                    SET lrn = ?, first_name = ?, middle_name = ?, last_name = ?, suffix = ?, gender = ?, birth_date = ?, age = ?, place_of_birth = ?, nationality = ?, religion = ?, address = ?, contact_number = ?
                    WHERE id = ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'sssssssisssssi',
                    $data['lrn'],
                    $data['first_name'],
                    $data['middle_name'],
                    $data['last_name'],
                    $data['suffix'],    
                    $data['gender'],
                    $data['birth_date'],
                    $data['age'],
                    $data['place_of_birth'],
                    $data['nationality'],
                    $data['religion'],
                    $data['address'],
                    $data['contact_number'],
                    $id
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }

        /**
         * Get student by ID
         * @param int $id Student ID
         * @return array|null Student data or null if not found
         */
        public function getById($id){
            try{
                $query = "SELECT * FROM {$this->students} WHERE id = ? LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }catch(Exception $e){
                error_log($e->getMessage());
                return null;
            }
        }

        public function delete($id){
            try{
                $query = "DELETE FROM {$this->students} WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }
    }