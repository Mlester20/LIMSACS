<?php
require_once __DIR__ . '/../models/Model.php';

    class AuditLogs extends Model {
        public function log($user_id, $role, $action, $module, $reference_id = null, $reference_table = null, $description = '', $status = 'success') {
            try {
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

                $query = "INSERT INTO audit_logs (user_id, role, action, module, reference_id, reference_table, description, ip_address, status, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $stmt = $this->con->prepare($query);

                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $this->con->error);
                }

                $stmt->bind_param(
                    "isssissss",
                    $user_id,
                    $role,
                    $action,
                    $module,
                    $reference_id,
                    $reference_table,
                    $description,
                    $ip,
                    $status
                );

                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                $stmt->close();
                return true;

            } catch (Exception $e) {
                error_log("Audit Log Error: " . $e->getMessage());
                return false;
            }
        }
    }

?>