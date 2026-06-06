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

        }

        public function update($id, $data){

        }

        public function delete($id){

        }
    }