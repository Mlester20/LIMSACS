<?php
require_once __DIR__ . '/../models/Model.php';

    class ParentGuardianService extends Model{
        protected $students = 'students';
        protected $parents_guardians = 'parents_guardians';

        public function searchParentsGuardians($keyword){
            $keyword = trim($keyword);

            if (empty($keyword) || strlen($keyword) < 2) {
                return [];
            }

            // Prepare the keyword for LIKE search
            $searchKeyword = '%' . $keyword . '%';
            $sql = "SELECT 
                        pg.id,
                        pg.student_id,
                        pg.father_name,
                        pg.father_occupation,
                        pg.father_contact,
                        pg.mother_name,
                        pg.mother_occupation,
                        pg.mother_contact,
                        pg.guardian_name,
                        pg.guardian_relationship,
                        pg.guardian_contact,
                        s.id AS student_id_from_table,
                        s.lrn,
                        s.first_name,
                        s.middle_name,
                        s.last_name,
                        s.suffix
                    FROM {$this->parents_guardians} pg
                    LEFT JOIN {$this->students} s ON pg.student_id = s.id
                    WHERE 
                        pg.father_name LIKE ? OR
                        pg.mother_name LIKE ? OR
                        pg.guardian_name LIKE ? OR
                        s.first_name LIKE ? OR
                        s.last_name LIKE ? OR
                        s.lrn LIKE ?
                    ORDER BY s.first_name ASC, s.last_name ASC";

            $stmt = null;
            $results = [];

            try {
                // Prepare the statement
                $stmt = $this->con->prepare($sql);

                if (!$stmt) {
                    return [];
                }
                $stmt->bind_param(
                    'ssssss', 
                    $searchKeyword, 
                    $searchKeyword, 
                    $searchKeyword, 
                    $searchKeyword, 
                    $searchKeyword, 
                    $searchKeyword
                );
                $stmt->execute();
                // Get the result
                $result = $stmt->get_result();
                $results = $result->fetch_all(MYSQLI_ASSOC);
            } catch (Exception $e) {
                error_log("Error " . $e->getMessage(), 500);
                return [];
            } finally {
                if ($stmt) {
                    $stmt->close();
                }
            }
            //return the results
            return $results;
        }
    }