<?php
require_once __DIR__ . '/../Model.php';

    class TeacherStudentsModel extends Model{
        protected $academic_history = 'academic_history';
        protected $students = 'students';
        protected $users = 'users';
        protected $sections = 'sections';
        protected $sy = 'school_year';

        /**
         * Get paginated students currently enrolled under one of this teacher's sections
         * @param int $teacher_id
         * @param int $limit
         * @param int $offset
         * @return array
         */
        public function getPaginated($teacher_id, $limit = 10, $offset = 0){
            try{
                $query = "SELECT
                        ah.id AS enrollment_id,
                        ah.student_id,
                        ah.enrollment_status,
                        ah.grade_level,
                        ah.created_at,
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
                        s.contact_number,
                        sy.school_year,
                        ss.section_name
                    FROM {$this->academic_history} ah
                    JOIN {$this->students} s ON ah.student_id = s.id
                    JOIN {$this->sections} ss ON ah.section_id = ss.id
                    LEFT JOIN {$this->sy} sy ON ah.school_year_id = sy.id
                    WHERE ss.adviser_id = ?
                    ORDER BY s.last_name ASC, s.first_name ASC
                    LIMIT ? OFFSET ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('iii', $teacher_id, $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return [];
            }
        }

        /**
         * Get total count of students assigned to this teacher's sections
         * @param int $teacher_id
         * @return int
         */
        public function getTotalCount($teacher_id){
            try{
                $query = "SELECT COUNT(*) as total
                    FROM {$this->academic_history} ah
                    JOIN {$this->sections} ss ON ah.section_id = ss.id
                    WHERE ss.adviser_id = ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $teacher_id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                return (int)($row['total'] ?? 0);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return 0;
            }
        }

        /**
         * Get a single student record by ID
         * @param int $id
         * @return array|null
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

        /**
         * Update a student's personal information
         * @param int $id
         * @param array $data
         * @return bool
         */
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
         * Verify a student is currently assigned to one of this teacher's sections
         * @param int $student_id
         * @param int $teacher_id
         * @return bool
         */
        public function isStudentOwnedByTeacher($student_id, $teacher_id){
            try{
                $query = "SELECT ah.id
                    FROM {$this->academic_history} ah
                    JOIN {$this->sections} ss ON ah.section_id = ss.id
                    WHERE ah.student_id = ? AND ss.adviser_id = ?
                    LIMIT 1
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('ii', $student_id, $teacher_id);
                $stmt->execute();
                return $stmt->get_result()->num_rows > 0;
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        /**
         * Get an enrollment record only if it belongs to a section advised by this teacher
         * @param int $enrollment_id academic_history.id
         * @param int $teacher_id
         * @return array|null
         */
        public function getEnrollmentOwnedByTeacher($enrollment_id, $teacher_id){
            try{
                $query = "SELECT ah.*, s.first_name, s.last_name
                    FROM {$this->academic_history} ah
                    JOIN {$this->students} s ON ah.student_id = s.id
                    JOIN {$this->sections} ss ON ah.section_id = ss.id
                    WHERE ah.id = ? AND ss.adviser_id = ?
                    LIMIT 1
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('ii', $enrollment_id, $teacher_id);
                $stmt->execute();
                return $stmt->get_result()->fetch_assoc();
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return null;
            }
        }
    }
