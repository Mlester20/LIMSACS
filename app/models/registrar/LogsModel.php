<?php
require_once __DIR__ . '/../Model.php';

class LogsModel extends Model {
    protected $audit_logs = 'audit_logs';
    protected $users = 'users';

    public function getLogs($user_id, $limit = 10, $offset = 0) {
        try {
            $query = "SELECT
                        al.*,
                        u.full_name AS user_full_name
                      FROM {$this->audit_logs} al
                      LEFT JOIN {$this->users} u ON al.user_id = u.id 
                      WHERE al.user_id = ?
                      ORDER BY al.created_at DESC
                      LIMIT ? OFFSET ?"; 
            
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("iii", $user_id, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            error_log("Error in LogsModel: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalCount($user_id) {
        try {
            $query = "SELECT COUNT(*) AS total FROM {$this->audit_logs} WHERE user_id = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return isset($row['total']) ? (int) $row['total'] : 0;
        } catch (Exception $e) {
            error_log("Error counting logs: " . $e->getMessage());
            return 0;
        }
    }
}
