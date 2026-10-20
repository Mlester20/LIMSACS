<?php
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../../app/controllers/registrar/EnrollmentController.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';

AuthRole::allowOnly(['registrar']);

try {
    $controller = new EnrollmentController($con);
    $activeSy = $controller->getActiveSchoolYear();
    $promotableSections = $controller->getPromotableSections();
} catch (Exception $e) {
    error_log($e->getMessage());
    $activeSy = null;
    $promotableSections = [];
}
?>

<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?= BASE_URL ?>/public/assets/"
  data-template="vertical-menu-template-free"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promote Students | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Csrf::token()); ?>">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/public/assets/img/favicon/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/demo.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/helpers.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/config.js"></script>
</head>
<body>
    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-1"><span class="text-muted fw-light">Enrollees /</span> Promote Students</h4>
        <p class="text-muted mb-4">Move every passing student in a section up to the next grade level at once, instead of editing enrollments one by one.</p>

        <?php if (!$activeSy): ?>
            <div class="alert alert-warning">
                <i class="bx bx-error-circle me-1"></i>
                No school year is currently active. Activate a school year first (and make sure the target grade level's sections already exist for it) before promoting students.
            </div>
        <?php elseif (empty($promotableSections)): ?>
            <div class="alert alert-info">
                <i class="bx bx-info-circle me-1"></i>
                No eligible sections to promote from. A section is eligible when it belongs to a <strong>past</strong> school year (not the currently active one, <?= htmlspecialchars($activeSy['school_year']) ?>) and isn't already Grade 6 (Grade 6 students graduate through the Enrollees page's "Mark as Graduated" action instead).
            </div>
        <?php else: ?>

            <div class="card">
                <div class="card-body">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="sourceSection" class="form-label fw-semibold">From (current section)</label>
                            <select class="form-select" id="sourceSection">
                                <option value="" selected disabled>Select a section to promote...</option>
                                <?php foreach ($promotableSections as $section): ?>
                                    <option
                                        value="<?= (int) $section['id'] ?>"
                                        data-school-year-id="<?= (int) $section['school_year_id'] ?>"
                                        data-grade-level="<?= htmlspecialchars($section['grade_level'], ENT_QUOTES) ?>"
                                        data-next-grade-level="<?= htmlspecialchars($section['next_grade_level'], ENT_QUOTES) ?>"
                                        data-section-name="<?= htmlspecialchars($section['section_name'], ENT_QUOTES) ?>"
                                    >
                                        <?= htmlspecialchars($section['grade_level'] . ' - ' . $section['section_name'] . ' (SY ' . $section['school_year'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="targetSection" class="form-label fw-semibold">To (target section, <?= htmlspecialchars($activeSy['school_year']) ?>)</label>
                            <select class="form-select" id="targetSection" disabled>
                                <option value="" selected>Select a source section first...</option>
                            </select>
                        </div>
                    </div>

                    <div id="promotionSummary" class="d-none alert alert-light border mb-3">
                        Promoting <strong id="summaryGradeFrom"></strong> &rarr; <strong id="summaryGradeTo"></strong>
                    </div>

                    <div id="studentChecklistWrap" class="d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllStudents" checked>
                                <label class="form-check-label fw-semibold" for="selectAllStudents">
                                    Select all (uncheck students who did not pass -- they'll stay behind, unaffected)
                                </label>
                            </div>
                            <span class="text-muted small" id="studentCountLabel"></span>
                        </div>

                        <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;"></th>
                                        <th>Student Name</th>
                                        <th>LRN</th>
                                    </tr>
                                </thead>
                                <tbody id="studentChecklistBody"></tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-primary" id="promoteBtn" onclick="submitPromotion()">
                                <i class="bx bx-up-arrow-alt me-1"></i>Promote Selected Students
                            </button>
                        </div>
                    </div>

                    <div id="promotionResult" class="d-none mt-3"></div>

                </div>
            </div>

        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
    <script>
        const ACTIVE_SY_ID = <?= json_encode($activeSy['id'] ?? null) ?>;
    </script>
    <script src="<?= BASE_URL ?>/public/js/registrar/promote-students.js"></script>
</body>
</html>
