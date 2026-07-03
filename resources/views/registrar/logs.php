<?php
require_once __DIR__ . '/../../../app/controllers/registrar/LogsController.php';
require_once __DIR__ . '/../../../app/middleware/auth.php';
AuthRole::allowOnly(['registrar']); 

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

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="card">
      <h5 class="card-header">Activity Logs</h5>
        <div class="table-responsive nowrap">
            <table class="table">
              <thead>
                <tr>
                  <th>Actions</th>
                  <th>Module</th>
                  <th>Reference Table</th>
                  <th>Description</th>
                  <th>IP Address</th>
                  <th>Status</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                <?php if(!empty($logs)): ?>
                  <?php foreach($logs as $log): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($log['action']); ?></td>
                      <td><?php echo htmlspecialchars($log['module']); ?></td>
                      <td><?php echo htmlspecialchars($log['reference_table']); ?></td>
                      <td><?php echo htmlspecialchars($log['description']); ?></td>
                      <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                      <td><?php echo htmlspecialchars($log['status']); ?></td>
                      <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center">No Logs Found.</td>
                    </tr>
                  <?php endif; ?>
              </tbody>
            </table>
        </div>

        <div class="card-footer">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <small class="text-muted">
                Showing
                <?php echo $pagination['totalRecords'] > 0 ? (($pagination['currentPage'] - 1) * $pagination['itemsPerPage']) + 1 : 0; ?>
                to
                <?php echo min($pagination['currentPage'] * $pagination['itemsPerPage'], $pagination['totalRecords']); ?>
                of
                <?php echo $pagination['totalRecords']; ?>
                entries
              </small>
            </div>

            <nav aria-label="Activity logs pagination">
              <ul class="pagination pagination-sm m-0">
                <?php if($pagination['hasPrevPage']): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['currentPage'] - 1; ?>">Previous</a>
                  </li>
                <?php else: ?>
                  <li class="page-item disabled">
                    <span class="page-link">Previous</span>
                  </li>
                <?php endif; ?>

                <?php
                  $startPage = max(1, $pagination['currentPage'] - 2);
                  $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);

                  if($startPage > 1):
                ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=1">1</a>
                  </li>
                  <?php if($startPage > 2): ?>
                    <li class="page-item disabled">
                      <span class="page-link">...</span>
                    </li>
                  <?php endif; ?>
                <?php endif; ?>

                <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                  <?php if($i == $pagination['currentPage']): ?>
                    <li class="page-item active">
                      <span class="page-link"><?php echo $i; ?></span>
                    </li>
                  <?php else: ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                  <?php endif; ?>
                <?php endfor; ?>

                <?php if($endPage < $pagination['totalPages']): ?>
                  <?php if($endPage < $pagination['totalPages'] - 1): ?>
                    <li class="page-item disabled">
                      <span class="page-link">...</span>
                    </li>
                  <?php endif; ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['totalPages']; ?>"><?php echo $pagination['totalPages']; ?></a>
                  </li>
                <?php endif; ?>

                <?php if($pagination['hasNextPage']): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['currentPage'] + 1; ?>">Next</a>
                  </li>
                <?php else: ?>
                  <li class="page-item disabled">
                    <span class="page-link">Next</span>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>
          </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    
    <!-- ── Vendor scripts ── -->
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>

    <!-- ── Chart.js ── -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script src="<?= BASE_URL ?>/public/js/admin/dashboard.js"></script>
</body>
</html>
