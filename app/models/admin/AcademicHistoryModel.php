<?php
require_once __DIR__ . '/../Model.php';

    class AcademicHistoryModel extends Model{
        protected $students = 'students';
        protected $academic_history = 'academic_history';
        protected $users = 'users';
        protected $section = 'sections';
        protected $school_year = 'school_year';

        public function getPaginated($limit, $offset, $search = '', $schoolYearId = ''){
            try{
                [$where, $params, $types] = $this->buildFilters($search, $schoolYearId);

                $query = "SELECT
                        ah.*,
                        s.first_name AS student_first_name,
                        s.last_name AS student_last_name,
                        u.full_name AS enrolled_by_registrar_name,
                        sec.section_name AS section_name,
                        sec.grade_level AS section_grade_level,
                        sy.school_year,

                        CONCAT(s.first_name, ' ', s.last_name) AS student_full_name
                        FROM {$this->academic_history} ah
                        LEFT JOIN {$this->students} s ON ah.student_id = s.id
                        LEFT JOIN {$this->users} u ON ah.enrolled_by = u.id
                        LEFT JOIN {$this->section} sec ON ah.section_id = sec.id
                        LEFT JOIN {$this->school_year} sy ON ah.school_year_id = sy.id
                        {$where}
                        ORDER BY ah.id ASC
                        LIMIT ? OFFSET ?
                    ";
                    $params[] = $limit;
                    $params[] = $offset;
                    $types .= 'ii';

                    $stmt = $this->con->prepare($query);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }

        /**
         * Get the total number of academic history records matching the given filters.
         * Needed to calculate total pages for pagination.
         */
        public function getTotalCount($search = '', $schoolYearId = ''){
            try{
                [$where, $params, $types] = $this->buildFilters($search, $schoolYearId);

                $query = "SELECT COUNT(*) AS total
                          FROM {$this->academic_history} ah
                          LEFT JOIN {$this->students} s ON ah.student_id = s.id
                          {$where}";
                $stmt = $this->con->prepare($query);
                if($types !== ''){
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                return (int)$row['total'];
            }catch(Exception $e){
                error_log($e->getMessage());
                return 0;
            }
        }

        private function buildFilters($search, $schoolYearId){
            $conditions = [];
            $params = [];
            $types = '';

            if($search !== ''){
                $conditions[] = "CONCAT(s.first_name, ' ', s.last_name) LIKE ?";
                $params[] = '%' . $search . '%';
                $types .= 's';
            }

            if($schoolYearId !== ''){
                $conditions[] = "ah.school_year_id = ?";
                $params[] = (int)$schoolYearId;
                $types .= 'i';
            }

            $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
            return [$where, $params, $types];
        }
    }