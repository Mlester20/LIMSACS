<?php
require_once __DIR__ . '/../Model.php';

    class AuditLogsModel extends Model{
        protected $audit_logs = 'audit_logs';
        protected $users = 'users';

        public function index(){
            try{
                $query = "SELECT audit_logs.*, users.full_name as user_fullName FROM {$this->audit_logs} JOIN {$this->users} ON audit_logs.user_id = users.id ORDER BY audit_logs.created_at ASC";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){   
                return $e->getMessage();
            }
        }

        public function delete($id){
            try{
                $query = "DELETE FROM {$this->audit_logs} WHERE id = ? ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log("Error deleting logs " . $e->getMessage());
                return false;
            }
        }
    }