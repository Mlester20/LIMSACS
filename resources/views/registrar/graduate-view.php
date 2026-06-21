<?php
require_once __DIR__ . '/../../../app/controllers/registrar/GraduatesController.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['registrar']);

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$graduate_id = (int) ($_GET['id'] ?? 0);
$profile     = $controller && $graduate_id ? $controller->profile($graduate_id) : null;

$docStatusBadge = static function (string $status): string {
    return match ($status) {
        'Verified'  => 'bg-label-success',
        'Pending'   => 'bg-label-warning',
        'Rejected'  => 'bg-label-danger',
        'Submitted' => 'bg-label-info',
        default     => 'bg-label-secondary',
    };
};

$enrollBadge = static function (string $status): string {
    return match ($status) {
        'Enrolled'    => 'bg-label-success',
        'Transferred' => 'bg-label-info',
        'Graduated'   => 'bg-label-primary',
        'Dropped'     => 'bg-label-danger',
        default       => 'bg-label-secondary',
    };
};
?>

<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../../public/assets/"
  data-template="vertical-menu-template-free"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Graduate Profile | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
    <link rel="icon" type="image/x-icon" href="../../../public/assets/img/favicon/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../../../public/assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="../../../public/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../../public/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../../public/assets/css/demo.css" />
    <link rel="stylesheet" href="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <script src="../../../public/assets/vendor/js/helpers.js"></script>
    <script src="../../../public/assets/js/config.js"></script>
</head>
<body>
    <?php require_once __DIR__ . '/../../../app/helpers/flashMessage.php'; FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h4 class="fw-bold mb-0">Graduate Profile</h4>
        <a href="graduates-master-list.php" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Master List
        </a>
    </div>

    <?php if (!$profile): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
            <i class="bx bx-error-circle fs-5"></i>
            <span>Graduate record not found.</span>
        </div>
    <?php else: ?>
        <?php $g = $profile['graduate']; ?>

        <div class="row g-4 mb-4">
            <!-- Personal Information -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold">Personal Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p class="text-muted small mb-0">LRN</p>
                                <p class="fw-semibold"><?= $e($g['lrn']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-0">Full Name</p>
                                <p class="fw-semibold"><?= $e(trim(($g['first_name'] ?? '') . ' ' . ($g['middle_name'] ?? '') . ' ' . ($g['last_name'] ?? '') . ' ' . ($g['suffix'] ?? ''))) ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted small mb-0">Gender</p>
                                <p class="fw-semibold"><?= $e($g['gender']) ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted small mb-0">Birth Date</p>
                                <p class="fw-semibold"><?= !empty($g['birth_date']) ? date('F j, Y', strtotime($g['birth_date'])) : '—' ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted small mb-0">Age</p>
                                <p class="fw-semibold"><?= $e($g['age'] ?? '—') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-0">Place of Birth</p>
                                <p class="fw-semibold"><?= $e($g['place_of_birth'] ?? '—') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-0">Nationality / Religion</p>
                                <p class="fw-semibold"><?= $e($g['nationality'] ?? '—') ?> / <?= $e($g['religion'] ?? '—') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-0">Contact Number</p>
                                <p class="fw-semibold"><?= $e($g['contact_number'] ?: '—') ?></p>
                            </div>
                            <div class="col-md-12">
                                <p class="text-muted small mb-0">Address</p>
                                <p class="fw-semibold"><?= $e($g['address'] ?? '—') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graduation Information -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold">Graduation Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar avatar-lg flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-medal bx-sm"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted small mb-0">Status</p>
                                <span class="badge <?= $enrollBadge($g['enrollment_status']) ?>"><?= $e($g['enrollment_status']) ?></span>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">Date Graduated</p>
                        <p class="fw-semibold"><?= !empty($g['graduation_date']) ? date('F j, Y', strtotime($g['graduation_date'])) : '—' ?></p>

                        <p class="text-muted small mb-0">School Year</p>
                        <p class="fw-semibold"><?= $e($g['school_year'] ?? '—') ?></p>

                        <p class="text-muted small mb-0">Grade Level &amp; Section</p>
                        <p class="fw-semibold"><?= $e($g['grade_level']) ?> &mdash; <?= $e($g['section_name'] ?? 'Unassigned') ?></p>

                        <p class="text-muted small mb-0">Honors</p>
                        <p class="fw-semibold"><?= $e($g['honors'] ?: '—') ?></p>

                        <p class="text-muted small mb-0">Recorded By (Registrar)</p>
                        <p class="fw-semibold"><?= $e($g['registrar_name'] ?? '—') ?></p>

                        <?php if (!empty($g['remarks'])): ?>
                            <p class="text-muted small mb-0">Remarks</p>
                            <p class="fw-semibold mb-0"><?= $e($g['remarks']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic History / Enrollment / Section History Timeline -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold">Academic History</h6>
                        <small class="text-muted">Enrollment and section history across all school years</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">School Year</th>
                                    <th>Grade Level</th>
                                    <th>Section</th>
                                    <th>Enrollment Status</th>
                                    <th class="pe-3">Date Enrolled</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($profile['academic_history'])): ?>
                                    <?php foreach ($profile['academic_history'] as $h): ?>
                                        <tr>
                                            <td class="ps-3"><?= $e($h['school_year'] ?? '—') ?></td>
                                            <td><?= $e($h['grade_level']) ?></td>
                                            <td><?= $e($h['section_name'] ?? 'Unassigned') ?></td>
                                            <td><span class="badge <?= $enrollBadge($h['enrollment_status']) ?>"><?= $e($h['enrollment_status']) ?></span></td>
                                            <td class="pe-3"><?= !empty($h['created_at']) ? date('F j, Y', strtotime($h['created_at'])) : '—' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">No academic history records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Submitted -->
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold">Documents Submitted</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Document</th>
                                    <th>Status</th>
                                    <th class="pe-3">Uploaded At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($profile['documents'])): ?>
                                    <?php foreach ($profile['documents'] as $doc): ?>
                                        <tr>
                                            <td class="ps-3"><?= $e($doc['document_name'] ?? '—') ?></td>
                                            <td><span class="badge <?= $docStatusBadge($doc['status']) ?>"><?= $e($doc['status']) ?></span></td>
                                            <td class="pe-3"><?= !empty($doc['uploaded_at']) ? date('F j, Y g:i A', strtotime($doc['uploaded_at'])) : '—' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-4">No documents submitted.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>
</body>
</html>
