<?php
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../models/teacher/DashboardModel.php';

class DashboardController
{
    private DashboardModel $dashboardModel;
    private int $teacherId;

    public function __construct($con, int $teacherId)
    {
        $this->dashboardModel = new DashboardModel($con);
        $this->teacherId      = $teacherId;
    }

    public function index(): array
    {
        $activeSchoolYear = $this->dashboardModel->getActiveSchoolYear();
        $sections         = $this->dashboardModel->getTeacherSections($this->teacherId);
        $totalStudents    = array_sum(array_column($sections, 'enrolled_count'));
        $gradeLevels      = array_unique(array_column($sections, 'grade_level'));

        return [
            'active_school_year' => $activeSchoolYear['school_year'] ?? null,
            'total_students'     => (int) $totalStudents,
            'sections'           => $sections,
            'grade_levels'       => $gradeLevels,
        ];
    }
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────
try {
    $controller = new DashboardController($con, (int) ($_SESSION['id'] ?? 0));
    $data       = $controller->index();
} catch (Throwable $dashboardException) {
    error_log('[teacher/DashboardController] ' . $dashboardException->getMessage()
        . ' in ' . $dashboardException->getFile()
        . ' on line ' . $dashboardException->getLine());
    $data = [
        'active_school_year' => null,
        'total_students'     => 0,
        'sections'           => [],
        'grade_levels'       => [],
        'error'              => 'Dashboard data could not be loaded. Please try again.',
    ];
}
