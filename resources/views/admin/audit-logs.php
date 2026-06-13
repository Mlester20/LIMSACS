<?php
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
require_once __DIR__ . '/../../../app/controllers/admin/AuditLogsController.php';
require_once __DIR__ . '/../../../app/middleware/auth.php';
AuthRole::allowOnly(['admin']);
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
    <title> Activity Logs | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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
    <link rel="stylesheet" href="../../../public/assets/vendor/libs/apex-charts/apex-charts.css" />
    <script src="../../../public/assets/vendor/js/helpers.js"></script>
    <script src="../../../public/assets/js/config.js"></script>
</head>
<body>

    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="card">
        <h5 class="card-header">Audit Logs</h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($paginated_logs)): ?>
                        <?php foreach ($paginated_logs as $log): ?>
                            <tr>
                                <td><?php echo $log['user_fullName']; ?></td>
                                <td><?php echo $log['role']; ?></td>
                                <td><?php echo $log['module']; ?></td>
                                <td><?php echo $log['description']; ?></td>
                                <td><?php echo $log['status']; ?></td>
                                <td>
                                    <form action="../../../app/controllers/admin/AuditLogsController.php" method="post" style="display: inline";>
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($log['id']); ?>">
                                        <button 
                                            type="submit" 
                                            class="btn btn-sm btn-danger"
                                            name="delete_logs"
                                            onclick="return confirm('Are you sure you to delete this log?, this action cannot be undone')"
                                        >
                                        Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No audit logs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end mb-0">

                    <!-- First & Previous -->
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1">First</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">First</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Previous</span>
                        </li>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php
                        $max_visible = 5;
                        $start_page = max(1, $current_page - floor($max_visible / 2));
                        $end_page   = min($total_pages, $start_page + $max_visible - 1);
                        $start_page = max(1, $end_page - $max_visible + 1);

                        for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?php echo ($i === $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next & Last -->
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?>">Last</a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Next</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Last</span>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>
            <p class="text-muted mb-0">
                Showing
                <?php echo $total_entries > 0 ? $offset + 1 : 0; ?>
                to
                <?php echo min($current_page * $entries_per_page, $total_entries); ?>
                of
                <?php echo $total_entries; ?>
                entries
            </p>
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    
    <!-- ── Vendor scripts ── -->
    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>
</body>
</html>