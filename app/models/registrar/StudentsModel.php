<?php
require_once __DIR__ . '/../Model.php';

    class StudentsModel extends Model{
        private $students = 'students';

        public function index(){
            try{
                $query = "SELECT * FROM {$this->students} ORDER BY last_name ASC";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();

                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                return $e->getMessage();
            }
        }

        /**
         * Get paginated students, with each student's most recent grade
         * level/section/enrollment status (from academic_history) attached
         * for display, and optional filtering by grade level/section/archived.
         * @param int $limit Records per page
         * @param int $offset Starting position
         * @param string $gradeLevel Exact grade_level match, or '' for all
         * @param string|int $sectionId Exact section_id match, or '' for all
         * @param string $archivedFilter '0' = active only (default), '1' = archived only, 'all' = both
         * @param string $gender 'Male' | 'Female', or '' for all
         * @return array Paginated student records
         */
        public function getPaginated($limit = 10, $offset = 0, $gradeLevel = '', $sectionId = '', $archivedFilter = '0', $gender = ''){
            try{
                [$where, $params, $types] = $this->buildStudentFilter($gradeLevel, $sectionId, $archivedFilter, $gender);

                $query = "
                    SELECT s.*, ah.grade_level AS current_grade_level, sec.section_name AS current_section_name, ah.enrollment_status AS current_enrollment_status
                    FROM {$this->students} s
                    LEFT JOIN academic_history ah ON ah.id = (
                        SELECT ah2.id FROM academic_history ah2 WHERE ah2.student_id = s.id ORDER BY ah2.id DESC LIMIT 1
                    )
                    LEFT JOIN sections sec ON ah.section_id = sec.id
                    {$where}
                    ORDER BY s.last_name ASC, s.first_name ASC
                    LIMIT ? OFFSET ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param($types . 'ii', ...array_merge($params, [$limit, $offset]));
                $stmt->execute();
                $result = $stmt->get_result();

                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                return $e->getMessage();
            }
        }

        /**
         * Every matching student, unpaginated and always alphabetical by last
         * name -- used for Print, so a roster larger than one page doesn't get
         * silently cut off.
         * @param string $gradeLevel Exact grade_level match, or '' for all
         * @param string|int $sectionId Exact section_id match, or '' for all
         * @param string $archivedFilter '0' = active only (default), '1' = archived only, 'all' = both
         * @param string $gender 'Male' | 'Female', or '' for all
         * @return array
         */
        public function getAllForPrint($gradeLevel = '', $sectionId = '', $archivedFilter = '0', $gender = ''){
            try{
                [$where, $params, $types] = $this->buildStudentFilter($gradeLevel, $sectionId, $archivedFilter, $gender);

                $query = "
                    SELECT s.*, ah.grade_level AS current_grade_level, sec.section_name AS current_section_name, ah.enrollment_status AS current_enrollment_status
                    FROM {$this->students} s
                    LEFT JOIN academic_history ah ON ah.id = (
                        SELECT ah2.id FROM academic_history ah2 WHERE ah2.student_id = s.id ORDER BY ah2.id DESC LIMIT 1
                    )
                    LEFT JOIN sections sec ON ah.section_id = sec.id
                    {$where}
                    ORDER BY s.last_name ASC, s.first_name ASC
                ";
                $stmt = $this->con->prepare($query);
                if($types !== ''){
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();

                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                return $e->getMessage();
            }
        }

        /**
         * Get total count of students, honoring the same filters as getPaginated().
         * @return int Total number of students
         */
        public function getTotalCount($gradeLevel = '', $sectionId = '', $archivedFilter = '0', $gender = ''){
            try{
                [$where, $params, $types] = $this->buildStudentFilter($gradeLevel, $sectionId, $archivedFilter, $gender);

                $query = "
                    SELECT COUNT(*) as total
                    FROM {$this->students} s
                    LEFT JOIN academic_history ah ON ah.id = (
                        SELECT ah2.id FROM academic_history ah2 WHERE ah2.student_id = s.id ORDER BY ah2.id DESC LIMIT 1
                    )
                    {$where}
                ";
                $stmt = $this->con->prepare($query);
                if($types !== ''){
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                return $row['total'];
            }catch(Exception $e){
                error_log($e->getMessage());
                return 0;
            }
        }

        /**
         * @return array{0:string,1:array,2:string} [WHERE clause, params, bind types]
         */
        private function buildStudentFilter($gradeLevel, $sectionId, $archivedFilter = '0', $gender = ''){
            $conditions = [];
            $params = [];
            $types = '';
            if($gradeLevel !== ''){
                $conditions[] = "ah.grade_level = ?";
                $params[] = $gradeLevel;
                $types .= 's';
            }
            if($sectionId !== ''){
                $conditions[] = "ah.section_id = ?";
                $params[] = $sectionId;
                $types .= 'i';
            }
            if($archivedFilter === '0' || $archivedFilter === '1'){
                $conditions[] = "s.archived = ?";
                $params[] = (int)$archivedFilter;
                $types .= 'i';
            }
            if($gender === 'Male' || $gender === 'Female'){
                $conditions[] = "s.gender = ?";
                $params[] = $gender;
                $types .= 's';
            }
            $where = $conditions ? "WHERE " . implode(' AND ', $conditions) : "";
            return [$where, $params, $types];
        }

        /**
         * Bulk set the archived flag for the given student ids.
         * @param int[] $ids
         * @param bool $archived
         * @return bool
         */
        public function bulkSetArchived(array $ids, bool $archived){
            if(empty($ids)) return false;
            try{
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $query = "UPDATE {$this->students} SET archived = ? WHERE id IN ({$placeholders})";
                $stmt = $this->con->prepare($query);
                $types = 'i' . str_repeat('i', count($ids));
                $archivedInt = $archived ? 1 : 0;
                $stmt->bind_param($types, $archivedInt, ...$ids);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }

        /**
         * Get student by LRN
         * @param string $lrn
         * @return array|null
         */
        public function getByLrn($lrn){
            try{
                $query = "SELECT * FROM {$this->students} WHERE lrn = ? LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('s', $lrn);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }catch(Exception $e){
                error_log($e->getMessage());
                return null;
            }
        }

        /**
         * Check if LRN exists
         * @param string $lrn
         * @return bool  
         */       
        public function lrnExists($lrn){
            try{
                $query = "SELECT id FROM {$this->students} WHERE lrn = ? LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('s', $lrn);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->num_rows > 0;
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        } 

        /**
         * All non-empty LRNs currently in the table, as a lookup set.
         * Used by bulk import to avoid one SELECT per row.
         * @return array<string,true>
         */
        public function getExistingLrns(){
            try{
                $query = "SELECT lrn FROM {$this->students} WHERE lrn IS NOT NULL AND lrn <> ''";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                $out = [];
                while($row = $result->fetch_assoc()){
                    $out[$row['lrn']] = true;
                }
                return $out;
            }catch(Exception $e){
                error_log($e->getMessage());
                return [];
            }
        }

        /**
         * Create new student record
         * @param array $data Student data
         */
        public function create($data){
            try{
                $query = "INSERT INTO {$this->students}(lrn, first_name, middle_name, last_name, suffix, gender, birth_date, age, place_of_birth, nationality, religion, address, contact_number) 
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'sssssssisssss',
                    $data['lrn'],
                    $data['first_name'],
                    $data['middle_name'],
                    $data['last_name'],
                    $data['suffix'],
                    $data['gender'],
                    $data['birth_date'],
                    $data['age'],
                    $data['place_of_birth'],
                    $data['nationality'],
                    $data['religion'],
                    $data['address'],
                    $data['contact_number']
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
                $query = "UPDATE {$this->students}
                    SET lrn = ?, first_name = ?, middle_name = ?, last_name = ?, suffix = ?, gender = ?, birth_date = ?, age = ?, place_of_birth = ?, nationality = ?, religion = ?, address = ?, contact_number = ?
                    WHERE id = ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    'sssssssisssssi',
                    $data['lrn'],
                    $data['first_name'],
                    $data['middle_name'],
                    $data['last_name'],
                    $data['suffix'],    
                    $data['gender'],
                    $data['birth_date'],
                    $data['age'],
                    $data['place_of_birth'],
                    $data['nationality'],
                    $data['religion'],
                    $data['address'],
                    $data['contact_number'],
                    $id
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }

        /**
         * Get student by ID
         * @param int $id Student ID
         * @return array|null Student data or null if not found
         */
        public function getById($id){
            try{
                $query = "SELECT * FROM {$this->students} WHERE id = ? LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }catch(Exception $e){
                error_log($e->getMessage());
                return null;
            }
        }

        public function delete($id){
            try{
                $query = "DELETE FROM {$this->students} WHERE id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }
    }