<?php
require_once __DIR__ . '/../Model.php';

    class GraduatesModel extends Model{
        protected $graduates = 'graduates';
        protected $students = 'students';
        protected $academic_history = 'academic_history';
        protected $sections = 'sections';
        protected $school_year = 'school_year';
        protected $users = 'users';
        protected $student_documents = 'student_documents';
        protected $document_types = 'document_types';

        // ── Master list ──────────────────────────────────────────────────────

        public function getPaginated($limit, $offset, $filters = []){
            try{
                [$where, $params, $types] = $this->buildFilters($filters);

                $query = "SELECT
                        g.id AS graduate_id,
                        g.student_id,
                        g.academic_history_id,
                        g.graduation_date,
                        g.honors,
                        g.remarks,
                        s.lrn,
                        s.gender,
                        CONCAT(s.first_name, ' ', s.last_name) AS student_full_name,
                        ah.grade_level,
                        ah.enrollment_status,
                        ah.school_year_id,
                        sec.section_name,
                        sy.school_year,
                        u.full_name AS registrar_name,
                        adv.full_name AS adviser_name
                    FROM {$this->graduates} g
                    JOIN {$this->students} s ON g.student_id = s.id
                    JOIN {$this->academic_history} ah ON g.academic_history_id = ah.id
                    LEFT JOIN {$this->sections} sec ON ah.section_id = sec.id
                    LEFT JOIN {$this->school_year} sy ON ah.school_year_id = sy.id
                    LEFT JOIN {$this->users} u ON g.recorded_by = u.id
                    LEFT JOIN {$this->users} adv ON sec.adviser_id = adv.id
                    {$where}
                    ORDER BY g.graduation_date DESC
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

        public function getTotalCount($filters = []){
            try{
                [$where, $params, $types] = $this->buildFilters($filters);

                $query = "SELECT COUNT(*) AS total
                    FROM {$this->graduates} g
                    JOIN {$this->students} s ON g.student_id = s.id
                    JOIN {$this->academic_history} ah ON g.academic_history_id = ah.id
                    LEFT JOIN {$this->sections} sec ON ah.section_id = sec.id
                    LEFT JOIN {$this->school_year} sy ON ah.school_year_id = sy.id
                    {$where}
                ";
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

        public function getAllForExport($filters = []){
            try{
                [$where, $params, $types] = $this->buildFilters($filters);

                $query = "SELECT
                        s.lrn,
                        CONCAT(s.first_name, ' ', s.last_name) AS student_full_name,
                        s.gender,
                        ah.grade_level,
                        ah.enrollment_status,
                        sec.section_name,
                        sy.school_year,
                        g.graduation_date,
                        u.full_name AS registrar_name,
                        adv.full_name AS adviser_name
                    FROM {$this->graduates} g
                    JOIN {$this->students} s ON g.student_id = s.id
                    JOIN {$this->academic_history} ah ON g.academic_history_id = ah.id
                    LEFT JOIN {$this->sections} sec ON ah.section_id = sec.id
                    LEFT JOIN {$this->school_year} sy ON ah.school_year_id = sy.id
                    LEFT JOIN {$this->users} u ON g.recorded_by = u.id
                    LEFT JOIN {$this->users} adv ON sec.adviser_id = adv.id
                    {$where}
                    ORDER BY g.graduation_date DESC
                ";
                $stmt = $this->con->prepare($query);
                if($types !== ''){
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log($e->getMessage());
                return [];
            }
        }

        private function buildFilters($filters){
            $conditions = [];
            $params = [];
            $types = '';

            $search = trim($filters['search'] ?? '');
            if($search !== ''){
                $conditions[] = "(s.lrn LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?)";
                $like = '%' . $search . '%';
                $params[] = $like;
                $params[] = $like;
                $types .= 'ss';
            }

            $schoolYearId = $filters['school_year_id'] ?? '';
            if($schoolYearId !== ''){
                $conditions[] = "ah.school_year_id = ?";
                $params[] = (int)$schoolYearId;
                $types .= 'i';
            }

            $gradeLevel = $filters['grade_level'] ?? '';
            if($gradeLevel !== ''){
                $conditions[] = "ah.grade_level = ?";
                $params[] = $gradeLevel;
                $types .= 's';
            }

            $sectionId = $filters['section_id'] ?? '';
            if($sectionId !== ''){
                $conditions[] = "ah.section_id = ?";
                $params[] = (int)$sectionId;
                $types .= 'i';
            }

            $gender = $filters['gender'] ?? '';
            if($gender !== ''){
                $conditions[] = "s.gender = ?";
                $params[] = $gender;
                $types .= 's';
            }

            $status = $filters['status'] ?? '';
            if($status !== ''){
                $conditions[] = "ah.enrollment_status = ?";
                $params[] = $status;
                $types .= 's';
            }

            $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
            return [$where, $params, $types];
        }

        // ── Dashboard stats ──────────────────────────────────────────────────

        public function getTotalGraduates(): int{
            $row = $this->con->query("SELECT COUNT(*) AS total FROM {$this->graduates}")->fetch_assoc();
            return (int)($row['total'] ?? 0);
        }

        public function getGenderCount($gender): int{
            try{
                $query = "SELECT COUNT(*) AS total
                    FROM {$this->graduates} g
                    JOIN {$this->students} s ON g.student_id = s.id
                    WHERE s.gender = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('s', $gender);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                return (int)($row['total'] ?? 0);
            }catch(Exception $e){
                error_log($e->getMessage());
                return 0;
            }
        }

        public function getCurrentSchoolYearGraduates(): int{
            $row = $this->con->query("
                SELECT COUNT(*) AS total
                FROM {$this->graduates} g
                JOIN {$this->academic_history} ah ON g.academic_history_id = ah.id
                JOIN {$this->school_year} sy ON ah.school_year_id = sy.id
                WHERE sy.status = 'active'
            ")->fetch_assoc();
            return (int)($row['total'] ?? 0);
        }

        public function getPreviousSchoolYearGraduates(): int{
            try{
                $activeRow = $this->con->query("
                    SELECT start_date FROM {$this->school_year}
                    WHERE status = 'active' ORDER BY start_date DESC LIMIT 1
                ")->fetch_assoc();

                if(!$activeRow){
                    return 0;
                }

                $stmt = $this->con->prepare("
                    SELECT id FROM {$this->school_year}
                    WHERE start_date < ? ORDER BY start_date DESC LIMIT 1
                ");
                $stmt->bind_param('s', $activeRow['start_date']);
                $stmt->execute();
                $prevSY = $stmt->get_result()->fetch_assoc();

                if(!$prevSY){
                    return 0;
                }

                $stmt = $this->con->prepare("
                    SELECT COUNT(*) AS total
                    FROM {$this->graduates} g
                    JOIN {$this->academic_history} ah ON g.academic_history_id = ah.id
                    WHERE ah.school_year_id = ?
                ");
                $stmt->bind_param('i', $prevSY['id']);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                return (int)($row['total'] ?? 0);
            }catch(Exception $e){
                error_log($e->getMessage());
                return 0;
            }
        }

        // ── Analytics ─────────────────────────────────────────────────────────

        public function getGraduatesPerSchoolYear(): array{
            $result = $this->con->query("
                SELECT sy.school_year, COUNT(*) AS total
                FROM {$this->graduates} g
                JOIN {$this->academic_history} ah ON g.academic_history_id = ah.id
                JOIN {$this->school_year} sy ON ah.school_year_id = sy.id
                GROUP BY sy.id, sy.school_year
                ORDER BY sy.start_date ASC
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getGraduatesPerGradeLevel(): array{
            $result = $this->con->query("
                SELECT ah.grade_level, COUNT(*) AS total
                FROM {$this->graduates} g
                JOIN {$this->academic_history} ah ON g.academic_history_id = ah.id
                GROUP BY ah.grade_level
                ORDER BY ah.grade_level ASC
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getGraduatesPerSection(): array{
            $result = $this->con->query("
                SELECT sec.section_name, sec.grade_level, COUNT(*) AS total
                FROM {$this->graduates} g
                JOIN {$this->academic_history} ah ON g.academic_history_id = ah.id
                LEFT JOIN {$this->sections} sec ON ah.section_id = sec.id
                GROUP BY sec.id, sec.section_name, sec.grade_level
                ORDER BY total DESC
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        // ── Graduate profile ─────────────────────────────────────────────────

        public function getGraduateProfile($graduateId){
            try{
                $query = "SELECT
                        g.id AS graduate_id,
                        g.student_id,
                        g.academic_history_id,
                        g.graduation_date,
                        g.honors,
                        g.remarks,
                        s.*,
                        ah.grade_level,
                        ah.enrollment_status,
                        ah.school_year_id,
                        sec.section_name,
                        sy.school_year,
                        u.full_name AS registrar_name
                    FROM {$this->graduates} g
                    JOIN {$this->students} s ON g.student_id = s.id
                    JOIN {$this->academic_history} ah ON g.academic_history_id = ah.id
                    LEFT JOIN {$this->sections} sec ON ah.section_id = sec.id
                    LEFT JOIN {$this->school_year} sy ON ah.school_year_id = sy.id
                    LEFT JOIN {$this->users} u ON g.recorded_by = u.id
                    WHERE g.id = ?
                    LIMIT 1
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $graduateId);
                $stmt->execute();
                return $stmt->get_result()->fetch_assoc();
            }catch(Exception $e){
                error_log($e->getMessage());
                return null;
            }
        }

        public function getAcademicHistoryByStudentId($studentId): array{
            try{
                $query = "SELECT
                        ah.*,
                        sy.school_year,
                        sec.section_name
                    FROM {$this->academic_history} ah
                    LEFT JOIN {$this->school_year} sy ON ah.school_year_id = sy.id
                    LEFT JOIN {$this->sections} sec ON ah.section_id = sec.id
                    WHERE ah.student_id = ?
                    ORDER BY ah.created_at ASC
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $studentId);
                $stmt->execute();
                return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log($e->getMessage());
                return [];
            }
        }

        public function getDocumentsByStudentId($studentId): array{
            try{
                $query = "SELECT
                        sd.*,
                        dt.document_name
                    FROM {$this->student_documents} sd
                    LEFT JOIN {$this->document_types} dt ON sd.document_type_id = dt.id
                    WHERE sd.student_id = ?
                    ORDER BY dt.document_name ASC
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param('i', $studentId);
                $stmt->execute();
                return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log($e->getMessage());
                return [];
            }
        }

        // ── Filter dropdown options ──────────────────────────────────────────

        public function getSchoolYearOptions(): array{
            $result = $this->con->query("SELECT id, school_year FROM {$this->school_year} ORDER BY start_date DESC");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getGradeLevelOptions(): array{
            $result = $this->con->query("
                SELECT DISTINCT ah.grade_level
                FROM {$this->graduates} g
                JOIN {$this->academic_history} ah ON g.academic_history_id = ah.id
                ORDER BY ah.grade_level ASC
            ");
            return $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'grade_level') : [];
        }

        public function getSectionOptions(): array{
            $result = $this->con->query("SELECT id, section_name, grade_level FROM {$this->sections} ORDER BY section_name ASC");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
    }
?>