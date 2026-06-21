<?php
require_once __DIR__ . '/../Model.php';

    class AuditLogsModel extends Model{
        protected $audit_logs = 'audit_logs';
        protected $users = 'users';

        /**
         * @param string $search matches user full_name, module, or description
         * @param int|null $limit pass null to fetch all matching rows (no pagination)
         */
        public function index($search = '', $limit = null, $offset = 0){
            try{
                [$where, $params, $types] = $this->buildFilters($search);

                $query = "SELECT audit_logs.*, users.full_name as user_fullName
                          FROM {$this->audit_logs}
                          JOIN {$this->users} ON audit_logs.user_id = users.id
                          {$where}
                          ORDER BY audit_logs.created_at DESC";
                if($limit !== null){
                    $query .= " LIMIT ? OFFSET ?";
                    $params[] = $limit;
                    $params[] = $offset;
                    $types .= 'ii';
                }

                $stmt = $this->con->prepare($query);
                if($types !== ''){
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }

        public function count($search = ''){
            try{
                [$where, $params, $types] = $this->buildFilters($search);

                $query = "SELECT COUNT(*) AS total
                          FROM {$this->audit_logs}
                          JOIN {$this->users} ON audit_logs.user_id = users.id
                          {$where}";
                $stmt = $this->con->prepare($query);
                if($types !== ''){
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                return (int)$row['total'];
            }catch(Exception $e){
                error_log($e->getMessage());
                return 0;
            }
        }

        private function buildFilters($search){
            if($search === ''){
                return ['', [], ''];
            }

            $like = '%' . $search . '%';
            $where = " WHERE (users.full_name LIKE ? OR audit_logs.module LIKE ? OR audit_logs.description LIKE ?)";
            return [$where, [$like, $like, $like], 'sss'];
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