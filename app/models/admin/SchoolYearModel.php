<?php
require_once __DIR__ . '/../Model.php';

    class SchoolYearModel extends Model{
        protected $sy = 'school_year';

        public function index(){
            try{
                $query = "SELECT * FROM {$this->sy} ORDER BY id DESC";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();

                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }

        public function create($data){
            try{
                $query = "INSERT INTO {$this->sy} (school_year, start_date, end_date, status) VALUES(?, ?, ?, ?)";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    "ssss",
                    $data['school_year'],
                    $data['start_date'],
                    $data['end_date'],  
                    $data['status']
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
                $query = "UPDATE {$this->sy} SET school_year = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    "ssssi",
                    $data['school_year'],
                    $data['start_date'],
                    $data['end_date'],
                    $data['status'],
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
                $query = "DELETE FROM {$this->sy} WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }
    }