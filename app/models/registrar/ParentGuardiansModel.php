<?php
require_once __DIR__ . '/../Model.php';

    class ParentGuardiansModel extends Model{
        protected $parents_guardians = 'parents_guardians';
        protected $students = 'students';

        public function index(){
            try{
                $query = "
                        SELECT pg.*, s.first_name AS student_first_name, s.last_name AS student_last_name FROM {$this->parents_guardians} pg JOIN {$this->students} s ON pg.student_id = s.id ORDER BY pg.id ASC
                        ";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                return $e->getMessage();
            }
        }

        public function getTotalCount(){
            try{
                $query = "SELECT COUNT(*) as total FROM {$this->parents_guardians}";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                return $row['total'];
            }catch(Exception $e){
                return 0;
            }
        }

        public function getWithPagination($offset, $limit){
            try{
                $query = "
                        SELECT pg.*, s.first_name AS student_first_name, s.last_name AS student_last_name FROM {$this->parents_guardians} pg JOIN {$this->students} s ON pg.student_id = s.id ORDER BY pg.id ASC LIMIT ? OFFSET ?
                        ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('ii', $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                return $e->getMessage();
            }
        }
        

        public function create($data){
            try{
                $query = "INSERT INTO {$this->parents_guardians} (student_id, father_name, father_occupation, father_contact, mother_name, mother_occupation, mother_contact, guardian_name, guardian_relationship, guardian_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'isssssssss', 
                    $data['student_id'], 
                    $data['father_name'], 
                    $data['father_occupation'], 
                    $data['father_contact'], 
                    $data['mother_name'], 
                    $data['mother_occupation'], 
                    $data['mother_contact'], 
                    $data['guardian_name'], 
                    $data['guardian_relationship'], 
                    $data['guardian_contact']
                );
                return $stmt->execute();
            }catch(Exception $e){
                return $e->getMessage();
            }
        }

        public function update($id, $data){
            try{
                $query = "UPDATE {$this->parents_guardians} SET student_id = ?, father_name = ?, father_occupation = ?, father_contact = ?, mother_name = ?, mother_occupation = ?, mother_contact = ?, guardian_name = ?, guardian_relationship = ?, guardian_contact = ? WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'isssssssssi', 
                    $data['student_id'], 
                    $data['father_name'], 
                    $data['father_occupation'], 
                    $data['father_contact'], 
                    $data['mother_name'], 
                    $data['mother_occupation'], 
                    $data['mother_contact'], 
                    $data['guardian_name'], 
                    $data['guardian_relationship'], 
                    $data['guardian_contact'], 
                    $id
                );
                return $stmt->execute();
            }catch(Exception $e){
                return $e->getMessage();
            }
        }

        public function delete($id){
            try{
                $query = "DELETE FROM {$this->parents_guardians} WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $id);
                return $stmt->execute();
            }catch(Exception $e){
                return $e->getMessage();
            }
        }
    }

    
?>