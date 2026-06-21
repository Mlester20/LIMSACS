<?php
require_once __DIR__ . '/../Model.php';

    class UsersModel extends Model{
        protected $users = 'users';

        /**
         * @param string $search matches full_name/email
         * @param string $role exact role filter
         * @param int|null $limit pass null to fetch all matching rows (no pagination)
         */
        public function index($search = '', $role = '', $limit = null, $offset = 0){
            try{
                [$where, $params, $types] = $this->buildFilters($search, $role);

                $query = "SELECT id, full_name, email, role, created_at FROM {$this->users}{$where} ORDER BY id ASC";
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

        public function count($search = '', $role = ''){
            try{
                [$where, $params, $types] = $this->buildFilters($search, $role);

                $query = "SELECT COUNT(*) AS total FROM {$this->users}{$where}";
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

        private function buildFilters($search, $role){
            $conditions = [];
            $params = [];
            $types = '';

            if($search !== ''){
                $conditions[] = "(full_name LIKE ? OR email LIKE ?)";
                $like = '%' . $search . '%';
                $params[] = $like;
                $params[] = $like;
                $types .= 'ss';
            }

            if($role !== ''){
                $conditions[] = "role = ?";
                $params[] = $role;
                $types .= 's';
            }

            $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
            return [$where, $params, $types];
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