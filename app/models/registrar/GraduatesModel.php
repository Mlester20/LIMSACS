<?php
require_once __DIR__ . '/../Model.php';

    class GraduatesModel extends Model {
        private $graduates = 'graduates';

        /**
         * Create a new graduate record
         * @param array $data Keys: student_id, academic_history_id, graduation_date, honors, remarks, recorded_by
         * @return bool
         */
        public function create($data) {
            try {
                $query = "INSERT INTO {$this->graduates}
                        (student_id, academic_history_id, graduation_date, honors, remarks, recorded_by, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";

                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'iisssi',
                    $data['student_id'],
                    $data['academic_history_id'],
                    $data['graduation_date'],
                    $data['honors'],
                    $data['remarks'],
                    $data['recorded_by']
                );

                return $stmt->execute();
            } catch (Exception $e) {
                error_log("Create graduate record error: " . $e->getMessage());
                return false;
            }
        }

        /**
         * Get all graduate records for a student
         * @param int $student_id
         * @return array
         */
        public function getByStudentId($student_id) {
            try {
                $query = "SELECT * FROM {$this->graduates} WHERE student_id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $student_id);
                $stmt->execute();
                $result = $stmt->get_result();

                return $result->fetch_all(MYSQLI_ASSOC);
            } catch (Exception $e) {
                error_log("Get graduates by student error: " . $e->getMessage());
                return [];
            }
        }

        /**
         * Check whether a graduate record already exists for an academic_history row
         * @param int $academic_history_id
         * @return array|null
         */
        public function findByAcademicHistoryId($academic_history_id) {
            try {
                $query = "SELECT * FROM {$this->graduates} WHERE academic_history_id = ? LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $academic_history_id);
                $stmt->execute();
                $result = $stmt->get_result();

                return $result->fetch_assoc();
            } catch (Exception $e) {
                error_log("Find graduate by academic history error: " . $e->getMessage());
                return null;
            }
        }
    }
?>
