<?php
require_once __DIR__ . '/../../../app/controllers/registrar/StudentImportController.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
require_once __DIR__ . '/../../../app/middleware/auth.php';
AuthRole::allowOnly(['registrar']);

$importResults = $_SESSION['import_results'] ?? null;
unset($_SESSION['import_results']);
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
    <title> Dashboard | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/libs/apex-charts/apex-charts.css" />
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/helpers.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/config.js"></script>
</head>
<body>

    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="card mb-3">
        <h5 class="card-header">Import Students</h5>
        <div class="card-body">
            <p class="text-muted">
                Upload an .xlsx, .xls, or .csv file to add students to the masters list.
                Existing students (matched by LRN) are skipped, never overwritten.
            </p>

            <form method="POST" enctype="multipart/form-data" action="<?= BASE_URL ?>/app/controllers/registrar/StudentImportController.php" class="row g-2 align-items-center">
                <?php echo Csrf::field(); ?>
                <input type="hidden" name="import_students" value="1">
                <div class="col-auto">
                    <input type="file" class="form-control" name="import_file" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
                <div class="col-auto">
                    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/app/controllers/registrar/StudentImportController.php?download=template">
                        Download Template
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if($importResults): ?>
    <div class="card">
        <h5 class="card-header">Import Results</h5>
        <div class="card-body">
            <div class="row text-center mb-3">
                <div class="col">
                    <div class="fw-bold fs-5"><?php echo (int)$importResults['total']; ?></div>
                    <div class="text-muted">Total Processed</div>
                </div>
                <div class="col">
                    <div class="fw-bold fs-5 text-success"><?php echo (int)$importResults['inserted']; ?></div>
                    <div class="text-muted">Inserted</div>
                </div>
                <div class="col">
                    <div class="fw-bold fs-5 text-warning"><?php echo (int)$importResults['skipped']; ?></div>
                    <div class="text-muted">Skipped</div>
                </div>
                <div class="col">
                    <div class="fw-bold fs-5 text-danger"><?php echo (int)$importResults['failed']; ?></div>
                    <div class="text-muted">Failed</div>
                </div>
            </div>

            <?php if($importResults['inserted'] > 0): ?>
                <div class="alert alert-info">
                    Students imported to the masters list. To record enrollment or graduation history for these students,
                    use the <a href="<?= BASE_URL ?>/resources/views/registrar/enrollment.php">Enrollment page</a>.
                </div>
            <?php endif; ?>

            <?php if(!empty($importResults['details'])): ?>
                <div class="table-responsive nowrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Row #</th>
                                <th>LRN</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($importResults['details'] as $detail): ?>
                                <tr>
                                    <td><?php echo (int)$detail['rowNumber']; ?></td>
                                    <td><?php echo htmlspecialchars($detail['lrn'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($detail['name']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($detail['status'])); ?></td>
                                    <td><?php echo htmlspecialchars($detail['reason'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <!-- ── Vendor scripts ── -->
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
</body>
</html>
