<?php
require_once __DIR__ . '/../Model.php';

    class SchoolYearModel extends Model{
        protected $sy = 'school_year';

        /**
         * @param string $search matches school_year
         * @param string $status exact status filter
         * @param int|null $limit pass null to fetch all matching rows (no pagination)
         */
        public function index($search = '', $status = '', $limit = null, $offset = 0){
            try{
                [$where, $params, $types] = $this->buildFilters($search, $status);

                $query = "SELECT * FROM {$this->sy}{$where} ORDER BY id DESC";
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

        public function count($search = '', $status = ''){
            try{
                [$where, $params, $types] = $this->buildFilters($search, $status);

                $query = "SELECT COUNT(*) AS total FROM {$this->sy}{$where}";
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

        private function buildFilters($search, $status){
            $conditions = [];
            $params = [];
            $types = '';

            if($search !== ''){
                $conditions[] = "school_year LIKE ?";
                $params[] = '%' . $search . '%';
                $types .= 's';
            }

            if($status !== ''){
                $conditions[] = "status = ?";
                $params[] = $status;
                $types .= 's';
            }

            $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
            return [$where, $params, $types];
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

        /**
         * Check if there's an active school year, excluding a specific ID if updating
         * @param string $status
         * @param int|null $except_id
         * @return bool
         */
        public function activeSy($status = 'active', $except_id = null) {
            try {

                $query = "SELECT * FROM {$this->sy} WHERE status = ?";
                if ($except_id) {
                    $query .= " AND id != ?";
                }

                $stmt = $this->con->prepare($query);

                if ($except_id) {
                    $stmt->bind_param("si", $status, $except_id); 
                } else {
                    $stmt->bind_param("s", $status);
                }

                $stmt->execute();
                $result = $stmt->get_result();
                return $result->num_rows > 0;
            } catch(Exception $e) {
                error_log('Error: ' . $e->getMessage());
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