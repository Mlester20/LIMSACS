<?php
require_once __DIR__ . '/../../models/Model.php'; 

    class DashboardModel extends Model{
        public function __construct($con)
        {
            parent::__construct($con);
        }

        // ── Stat counters ──────────────────────────────────────────────────────────

        public function getTotalStudents(): int
        {
            $row = $this->con->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getTotalSections(): int
        {
            $row = $this->con->query("SELECT COUNT(*) AS total FROM sections")->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getTotalActiveSchoolYears(): int
        {
            $row = $this->con->query(
                "SELECT COUNT(*) AS total FROM school_year WHERE status = 'active'"
            )->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getTotalRequiredDocuments(): int
        {
            $row = $this->con->query(
                "SELECT COUNT(*) AS total FROM document_types WHERE is_required = 1 AND is_active = 1"
            )->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getCurrentEnrolledStudents(): int
        {
            $row = $this->con->query("
                SELECT COUNT(*) AS total
                FROM   academic_history ah
                INNER JOIN school_year sy ON sy.id = ah.school_year_id
                WHERE  sy.status            = 'active'
                AND  ah.enrollment_status = 'Enrolled'
            ")->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        // ── Detail tables ──────────────────────────────────────────────────────────

        public function getRecentStudentRegistrations(): array
        {
            $result = $this->con->query("
                SELECT lrn,
                    CONCAT(first_name, ' ', last_name) AS full_name,
                    gender,
                    contact_number,
                    created_at
                FROM   students
                ORDER  BY created_at DESC
                LIMIT  10
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getRecentDocumentUploads(): array
        {
            // Column is `document_name`, not `name` — confirmed from schema.
            $result = $this->con->query("
                SELECT CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                    dt.document_name,
                    sd.status,
                    sd.uploaded_at
                FROM   student_documents sd
                INNER JOIN document_types dt ON dt.id = sd.document_type_id
                INNER JOIN students        s  ON s.id  = sd.student_id
                ORDER  BY sd.uploaded_at DESC
                LIMIT  10
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getGradeLevelSummary(): array
        {
            $result = $this->con->query("
                SELECT grade_level,
                    COUNT(*) AS total_students
                FROM   academic_history
                GROUP  BY grade_level
                ORDER  BY grade_level ASC
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getDocumentStatusSummary(): array
        {
            $result = $this->con->query("
                SELECT status,
                    COUNT(*) AS total
                FROM   student_documents
                GROUP  BY status
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getMonthlyRegistrationTrend(int $months = 6): array
        {
            $months = max(1, $months);

            // Aggregate actual counts per year-month bucket.
            $stmt = $this->con->prepare("
                SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym,
                    COUNT(*) AS total
                FROM   students
                WHERE  created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP  BY ym
            ");
            $stmt->bind_param('i', $months);
            $stmt->execute();
            $result = $stmt->get_result();
            $rows   = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $counts = array_column($rows, 'total', 'ym');

            // Backfill every month in the window so gaps render as zero, not gaps.
            $trend = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $ym = date('Y-m', strtotime("-{$i} months"));
                $trend[] = [
                    'month' => date('M Y', strtotime($ym . '-01')),
                    'total' => (int) ($counts[$ym] ?? 0),
                ];
            }
            return $trend;
        }

        public function getEnrollmentStatusBreakdown(): array
        {
            $result = $this->con->query("
                SELECT enrollment_status AS status,
                    COUNT(*) AS total
                FROM   academic_history
                GROUP  BY enrollment_status
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getTotalGraduates(): int
        {
            $row = $this->con->query("SELECT COUNT(*) AS total FROM graduates")->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getGraduatesActiveSchoolYear(): int
        {
            $row = $this->con->query("
                SELECT COUNT(*) AS total
                FROM   graduates g
                INNER JOIN academic_history ah ON ah.id = g.academic_history_id
                INNER JOIN school_year      sy ON sy.id = ah.school_year_id
                WHERE  sy.status = 'active'
            ")->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getSectionCapacityUtilization(): array
        {
            $result = $this->con->query("
                SELECT sec.section_name,
                    sec.grade_level,
                    sec.max_students,
                    COUNT(ah.id) AS enrolled_count
                FROM   sections sec
                INNER JOIN school_year sy ON sy.id = sec.school_year_id AND sy.status = 'active'
                LEFT  JOIN academic_history ah
                       ON ah.section_id = sec.id AND ah.enrollment_status = 'Enrolled'
                GROUP  BY sec.id, sec.section_name, sec.grade_level, sec.max_students
                ORDER  BY (COUNT(ah.id) / NULLIF(sec.max_students, 0)) DESC
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
    }