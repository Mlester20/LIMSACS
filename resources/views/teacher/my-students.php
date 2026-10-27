<?php
require_once __DIR__ . '/../../../app/controllers/teacher/StudentsController.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['teacher']);

$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$section = $myStudentsData['section'] ?? null;
$students = $myStudentsData['students'] ?? [];
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
    <title>My Students | <?php  require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
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
</head>
<body>

    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-0">My Students</h4>
        <?php if ($section): ?>
            <p class="text-muted mb-4">
                <?= $e($section['grade_level']) ?> - <?= $e($section['section_name']) ?> &middot; SY <?= $e($section['school_year']) ?>
            </p>
        <?php endif; ?>

        <?php if (!$section): ?>
            <div class="alert alert-warning">
                <i class="bx bx-error-circle me-1"></i>
                You are not currently assigned as a section adviser for the active school year.
            </div>
        <?php else: ?>
            <div class="card">
                <h5 class="card-header d-flex justify-content-between align-items-center">
                    <span>Enrolled Students</span>
                    <span class="badge bg-label-primary rounded-pill"><?= count($students) ?></span>
                </h5>
                <div class="table-responsive nowrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>LRN</th>
                                <th>Student Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Contact</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $i => $student): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= $e($student['lrn']) ?></td>
                                        <td class="fw-semibold"><?= $e($student['last_name'] . ', ' . $student['first_name']) ?></td>
                                        <td><?= $e($student['gender']) ?></td>
                                        <td><?= $e($student['age']) ?></td>
                                        <td><?= $e($student['contact_number'] ?: '—') ?></td>
                                        <td><?= $e($student['address'] ?: '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bx bx-user-x fs-3 d-block mb-1"></i>
                                        No students enrolled in your section yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
