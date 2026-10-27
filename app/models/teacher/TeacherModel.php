<?php
require_once __DIR__ . '/../Model.php';

    /**
     * Everything a logged-in teacher is allowed to see is scoped to the single
     * section they currently advise (sections.adviser_id = their user id, for
     * the active school year) -- a teacher with no section assigned simply sees
     * empty data everywhere, never another adviser's students.
     */
    class TeacherModel extends Model{
        protected $sections = 'sections';
        protected $students = 'students';
        protected $academic_history = 'academic_history';
        protected $student_documents = 'student_documents';
        protected $school_year = 'school_year';

        /**
         * The section this teacher currently advises, for the active school
         * year, or null if they have none (or no school year is active yet).
         * @param int $teacherId
         * @return array|null
         */
        public function getAssignedSection($teacherId){
            try{
                $query = "SELECT sec.*, sy.school_year
                    FROM {$this->sections} sec
                    JOIN {$this->school_year} sy ON sec.school_year_id = sy.id
                    WHERE sec.adviser_id = ? AND sy.status = 'active'
                    LIMIT 1";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $teacherId);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc() ?: null;
            }catch(Exception $e){
                error_log('Get assigned section error: ' . $e->getMessage());
                return null;
            }
        }

        /**
         * Students currently Enrolled in the given section/school year.
         * @param int $sectionId
         * @param int $schoolYearId
         * @return array
         */
        public function getMyStudents($sectionId, $schoolYearId){
            try{
                $query = "SELECT
                        ah.id AS enrollment_id,
                        s.id AS student_id,
                        s.lrn,
                        s.first_name,
                        s.middle_name,
                        s.last_name,
                        s.gender,
                        s.age,
                        s.contact_number,
                        s.address
                    FROM {$this->academic_history} ah
                    JOIN {$this->students} s ON ah.student_id = s.id
                    WHERE ah.section_id = ? AND ah.school_year_id = ? AND ah.enrollment_status = 'Enrolled'
                    ORDER BY s.last_name ASC, s.first_name ASC";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('ii', $sectionId, $schoolYearId);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log('Get my students error: ' . $e->getMessage());
                return [];
            }
        }

        /**
         * Dashboard counts scoped to this section: total/male/female students,
         * and document status breakdown for those students' documents.
         * @param int $sectionId
         * @param int $schoolYearId
         * @return array
         */
        public function getDashboardStats($sectionId, $schoolYearId){
            $stats = [
                'total_students' => 0,
                'male_students' => 0,
                'female_students' => 0,
                'pending_documents' => 0,
                'submitted_documents' => 0,
                'verified_documents' => 0,
                'rejected_documents' => 0,
            ];

            try{
                $query = "SELECT s.gender, COUNT(*) AS total
                    FROM {$this->academic_history} ah
                    JOIN {$this->students} s ON ah.student_id = s.id
                    WHERE ah.section_id = ? AND ah.school_year_id = ? AND ah.enrollment_status = 'Enrolled'
                    GROUP BY s.gender";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('ii', $sectionId, $schoolYearId);
                $stmt->execute();
                $result = $stmt->get_result();
                while($row = $result->fetch_assoc()){
                    $stats['total_students'] += (int) $row['total'];
                    if($row['gender'] === 'Male'){
                        $stats['male_students'] = (int) $row['total'];
                    }elseif($row['gender'] === 'Female'){
                        $stats['female_students'] = (int) $row['total'];
                    }
                }

                $docQuery = "SELECT sd.status, COUNT(*) AS total
                    FROM {$this->student_documents} sd
                    WHERE sd.student_id IN (
                        SELECT ah2.student_id FROM {$this->academic_history} ah2
                        WHERE ah2.section_id = ? AND ah2.school_year_id = ? AND ah2.enrollment_status = 'Enrolled'
                    )
                    GROUP BY sd.status";
                $stmt = $this->con->prepare($docQuery);
                $stmt->bind_param('ii', $sectionId, $schoolYearId);
                $stmt->execute();
                $result = $stmt->get_result();
                while($row = $result->fetch_assoc()){
                    $key = strtolower($row['status']) . '_documents';
                    if(array_key_exists($key, $stats)){
                        $stats[$key] = (int) $row['total'];
                    }
                }
            }catch(Exception $e){
                error_log('Get teacher dashboard stats error: ' . $e->getMessage());
            }

            return $stats;
        }
    }
