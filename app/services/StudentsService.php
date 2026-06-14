<?php
require_once __DIR__ . '/../models/Model.php';

    class StudentsService extends Model{
        protected $students = 'students';

        /**
         * Create full name from student name components
         * @param string $firstName
         * @param string $middleName
         * @param string $lastName
         * @param string $suffix
         * @return string Full name
         */

        public static function getFullName($firstName, $middleName = '', $lastName = '', $suffix = '') {
            $nameParts = [];
            
            if (!empty($firstName)) $nameParts[] = $firstName;
            if (!empty($middleName)) $nameParts[] = $middleName;
            if (!empty($lastName)) $nameParts[] = $lastName;
            if (!empty($suffix)) $nameParts[] = $suffix;
            
            return implode(' ', $nameParts);
        }

        /**
         * Add full name to student records
         * @param array $students Array of student records
         * @return array Students with full_name added
        */
        public static function addFullNames($students) {
            foreach ($students as &$student) {
                $student['full_name'] = self::getFullName(
                    $student['first_name'] ?? '',
                    $student['middle_name'] ?? '',
                    $student['last_name'] ?? '',
                    $student['suffix'] ?? ''
                );
            }
            return $students;
        }

        /**
         * Search Specific Students
         * Prepare the Like keyword
        */
        public function searchStudents($keyword){
            $keyword = trim($keyword);

            if(empty($keyword) || strlen($keyword) < 2){
                return []; //simply returns an empty array
            }

            //prepare the keyword for LIKE search
            $searchKeyword = '%' . $keyword . '%';
            $query = "SELECT
                s.id,
                s.lrn,
                s.first_name,
                s.middle_name,
                s.last_name,
                s.suffix,
                s.gender,
                s.birth_date,
                s.age,
                s.place_of_birth,
                s.nationality,
                s.religion,
                s.address,
                s.contact_number
                FROM {$this->students} s
                WHERE 
                s.lrn LIKE ? OR
                s.first_name LIKE ? OR
                s.middle_name LIKE ? OR
                s.last_name LIKE ?
                ORDER BY s.first_name ASC, s.last_name ASC
            ";

            $stmt = null;
            $result = [];

            try{
                //prepare the statement
                $stmt = $this->con->prepare($query);

                //check if stmt is not successfully
                if(!$stmt){
                    return []; //return an empty array
                }

                $stmt->bind_param(
                    "ssss",
                    $searchKeyword, $searchKeyword, $searchKeyword, $searchKeyword
                );
                $stmt->execute();

                //get the result
                $result = $stmt->get_result();
                $results = $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Search Students Error: " . $e->getMessage());
                return [];
            }finally{
                if($stmt){
                    $stmt->close();
                }
            }
            //return the results
            return $results ?? [];
        }

        /**
         * Search students who do NOT yet have a guardian record
         * @param string $keyword
         * @return array
         */
        public function searchAvailableStudents($keyword) {
            $keyword = trim($keyword);

            if (empty($keyword) || strlen($keyword) < 2) {
                return [];
            }

            $searchKeyword = '%' . $keyword . '%';

            $query = "
                SELECT s.id, s.lrn, s.first_name, s.middle_name, s.last_name
                FROM {$this->students} s
                WHERE (
                    s.first_name LIKE ? OR
                    s.last_name  LIKE ? OR
                    s.lrn        LIKE ?
                )
                AND NOT EXISTS (
                    SELECT 1 FROM parents_guardians pg WHERE pg.student_id = s.id
                )
                ORDER BY s.first_name ASC, s.last_name ASC
                LIMIT 10
            ";

            try {
                $stmt = $this->con->prepare($query);
                if (!$stmt) return [];

                $stmt->bind_param('sss', $searchKeyword, $searchKeyword, $searchKeyword);
                $stmt->execute();
                $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                return $results ?? [];
            } catch (Exception $e) {
                error_log("searchAvailableStudents error: " . $e->getMessage());
                return [];
            }
        }

    }