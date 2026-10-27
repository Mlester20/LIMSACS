<?php

require_once __DIR__ . '/../../../app/controllers/teacher/DashboardController.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['teacher']);

$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$section = $dashboardData['section'] ?? null;
$stats = $dashboardData['stats'] ?? [];
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
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title>Dashboard | <?php  require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
    <meta name="description" content="" />
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
    <style>
        .dashboard-stat-card { cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease; }
        .dashboard-stat-card:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,.1) !important; }
    </style>
</head>
<body>

    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold mb-1">Welcome, <?= $e($_SESSION['full_name'] ?? 'Teacher') ?></h4>

        <?php if (!$section): ?>
            <div class="alert alert-warning mt-3">
                <i class="bx bx-error-circle me-1"></i>
                You are not currently assigned as a section adviser for the active school year. Contact the registrar if this is unexpected.
            </div>
        <?php else: ?>
            <p class="text-muted mb-4">
                Adviser of <strong><?= $e($section['grade_level']) ?> - <?= $e($section['section_name']) ?></strong>
                &middot; SY <?= $e($section['school_year']) ?>
            </p>

            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-4">
                    <a href="<?= BASE_URL ?>/resources/views/teacher/my-students.php" class="text-decoration-none">
                        <div class="card dashboard-stat-card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="avatar avatar-lg flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-group bx-sm"></i></span>
                                </div>
                                <div>
                                    <p class="text-muted small mb-0">Total Students</p>
                                    <h3 class="mb-0 fw-bold"><?= (int) ($stats['total_students'] ?? 0) ?></h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="avatar avatar-lg flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-info"><i class="bx bx-male-sign bx-sm"></i></span>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">Male</p>
                                <h3 class="mb-0 fw-bold"><?= (int) ($stats['male_students'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="avatar avatar-lg flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-pink"><i class="bx bx-female-sign bx-sm"></i></span>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">Female</p>
                                <h3 class="mb-0 fw-bold"><?= (int) ($stats['female_students'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-3">Document Requirements Status</h6>
            <div class="row g-4">
                <div class="col-sm-6 col-xl-3">
                    <a href="<?= BASE_URL ?>/resources/views/teacher/documents.php?status=Submitted" class="text-decoration-none">
                        <div class="card dashboard-stat-card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="avatar avatar-lg flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-info"><i class="bx bx-upload bx-sm"></i></span>
                                </div>
                                <div>
                                    <p class="text-muted small mb-0">Submitted</p>
                                    <h3 class="mb-0 fw-bold"><?= (int) ($stats['submitted_documents'] ?? 0) ?></h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <a href="<?= BASE_URL ?>/resources/views/teacher/documents.php?status=Pending" class="text-decoration-none">
                        <div class="card dashboard-stat-card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="avatar avatar-lg flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five bx-sm"></i></span>
                                </div>
                                <div>
                                    <p class="text-muted small mb-0">Pending</p>
                                    <h3 class="mb-0 fw-bold"><?= (int) ($stats['pending_documents'] ?? 0) ?></h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <a href="<?= BASE_URL ?>/resources/views/teacher/documents.php?status=Verified" class="text-decoration-none">
                        <div class="card dashboard-stat-card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="avatar avatar-lg flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle bx-sm"></i></span>
                                </div>
                                <div>
                                    <p class="text-muted small mb-0">Verified</p>
                                    <h3 class="mb-0 fw-bold"><?= (int) ($stats['verified_documents'] ?? 0) ?></h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <a href="<?= BASE_URL ?>/resources/views/teacher/documents.php?status=Rejected" class="text-decoration-none">
                        <div class="card dashboard-stat-card border-0 shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="avatar avatar-lg flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x-circle bx-sm"></i></span>
                                </div>
                                <div>
                                    <p class="text-muted small mb-0">Rejected</p>
                                    <h3 class="mb-0 fw-bold"><?= (int) ($stats['rejected_documents'] ?? 0) ?></h3>
                                </div>
                            </div>
                        </div>
                    </a>
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
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
</html>
