<?php
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../core/errorHandler.php';
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
    $data = [
        'active_school_year' => null,
        'total_students'     => 0,
        'sections'           => [],
        'grade_levels'       => [],
        'error'              => ErrorHandler::safeMessage(
            $dashboardException,
            'Dashboard data could not be loaded. Please try again.',
            'teacher/DashboardController::index'
        ),
    ];
}
