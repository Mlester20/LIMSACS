<?php
require_once __DIR__ . '/../Model.php';

    class UsersModel extends Model{
        protected $users = 'users';

        public function index(){
            try{
                $query = "SELECT id, full_name, email, role, created_at FROM {$this->users} ORDER BY id ASC";
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
                $query = "INSERT INTO {$this->users} (full_name, email, password, role, profile_picture) VALUES(?, ?, ?, ?, ?)";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    "sssss",
                    $data['full_name'],
                    $data['email'],
                    $data['password'],
                    $data['role'],
                    $data['profile_picture']
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
                $query = "UPDATE {$this->users} SET full_name = ?, email = ?, role = ? WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    "sssi",
                    $data['full_name'],
                    $data['email'],
                    $data['role'],
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
                $query = "DELETE FROM {$this->users} WHERE id = ?";
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