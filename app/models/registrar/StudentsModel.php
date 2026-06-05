<?php
require_once __DIR__ . '/../Model.php';

    class StudentsModel extends Model{
        private $students = 'students';

        public function index(){
            try{
                $query = "SELECT * FROM {$this->students} ORDER BY id DESC";
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
                $query = "SELECT * FROM {$this->students} ORDER BY id DESC LIMIT ? OFFSET ?";
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
                $query = "SELECT COUNT(*) as total FROM {$this->students}";
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

        public function create($data){
            try{
                $query = "INSERT
                    INTO students(lrn, first_name, middle_name, last_name, suffix, grade_level, gender, birth_date, age, place_of_birth, nationality, religion, address, contact_number, enrollment_status) 
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'ssssssssissssss',
                    $data['lrn'],
                    $data['first_name'],
                    $data['middle_name'],
                    $data['last_name'],
                    $data['suffix'],
                    $data['grade_level'],
                    $data['gender'],
                    $data['birth_date'],
                    $data['age'],
                    $data['place_of_birth'],
                    $data['nationality'],
                    $data['religion'],
                    $data['address'],
                    $data['contact_number'],
                    $data['enrollment_status']
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
                $query = "UPDATE
                    students
                    SET lrn = ?, first_name = ?, middle_name = ?, last_name = ?, suffix = ?, grade_level = ?, gender = ?, birth_date = ?, age = ?, place_of_birth = ?, nationality = ?, religion = ?, address = ?, contact_number = ?, enrollment_status = ?
                    WHERE id = ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'ssssssssisssssi',
                    $data['lrn'],
                    $data['first_name'],
                    $data['middle_name'],
                    $data['last_name'],
                    $data['suffix'],
                    $data['grade_level'],
                    $data['gender'],
                    $data['birth_date'],
                    $data['age'],
                    $data['place_of_birth'],
                    $data['nationality'],
                    $data['religion'],
                    $data['address'],
                    $data['contact_number'],
                    $data['enrollment_status'],
                    $id
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
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