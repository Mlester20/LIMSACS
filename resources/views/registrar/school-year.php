<?php
require_once __DIR__ . '/../../../app/controllers/registrar/SchoolYearController.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
require_once __DIR__ . '/../../../app/middleware/auth.php';
AuthRole::allowOnly(['registrar']); 
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
    <title> School Year | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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


    <!-- Search / Filter + Add School Year -->
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <input
                type="text"
                name="search"
                class="form-control"
                style="max-width: 220px;"
                placeholder="Search school year"
                value="<?php echo htmlspecialchars($search_term); ?>">

            <select name="status" class="form-select" style="max-width: 160px;">
                <option value="">All Statuses</option>
                <?php foreach (['active', 'inactive', 'archived'] as $statusOption): ?>
                    <option value="<?php echo $statusOption; ?>" <?php echo $status_filter === $statusOption ? 'selected' : ''; ?>>
                        <?php echo ucfirst($statusOption); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-outline-secondary">Filter</button>
            <?php if ($search_term !== '' || $status_filter !== ''): ?>
                <a href="school-year.php" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addSchoolYearModal">
            Add School Year
        </button>
    </div>

    <!-- Add School Year Modal -->
    <div class="modal fade" id="addSchoolYearModal" tabindex="-1" aria-labelledby="addSchoolYearModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="../../../app/controllers/registrar/SchoolYearController.php" method="POST">
                <?php echo Csrf::field(); ?>

                <div class="modal-content">
                    <div class="modal-header text-white">
                        <h5 class="modal-title" id="addSchoolYearModalLabel">
                            Add School Year
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <!-- School Year -->
                        <div class="mb-3">
                            <label for="school_year" class="form-label">
                                School Year
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="school_year"
                                name="school_year"
                                placeholder="e.g. 2025-2026"
                                required
                            >
                        </div>

                        <!-- Start Date -->
                        <div class="mb-3">
                            <label for="start_date" class="form-label">
                                Start Date
                            </label>
                            <input
                                type="date"
                                class="form-control"
                                id="start_date"
                                name="start_date"
                                required
                            >
                        </div>

                        <!-- End Date -->
                        <div class="mb-3">
                            <label for="end_date" class="form-label">
                                End Date
                            </label>
                            <input
                                type="date"
                                class="form-control"
                                id="end_date"
                                name="end_date"
                                required
                            >
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                Status
                            </label>
                            <select
                                class="form-select"
                                id="status"
                                name="status"
                                required>
                                <option value="" selected disabled>Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary"
                            name="create_sy"
                        >
                            Save School Year
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- update modal -->
    <!-- edit sy modal -->
    <div class="modal fade" id="editSyModal" tabindex="-1" aria-labelledby="editSyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSyModalLabel">Edit School Year</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../../app/controllers/registrar/SchoolYearController.php" method="POST">
                    <?php echo Csrf::field(); ?>
                    <input type="hidden" name="id" id="edit_sy_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_school_year" class="form-label">School Year</label>
                            <input type="text" class="form-control" id="edit_school_year" name="school_year" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="update_sy">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card mt-4">
      <h5 class="card-header">School Year</h5>
      <div class="table-responsive nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>School Year</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
                <?php if (!empty($school_years)): ?>
                    <?php foreach ($school_years as $sy): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sy['id']); ?></td>
                            <td><?php echo htmlspecialchars($sy['school_year']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($sy['start_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($sy['end_date'])); ?></td>
                            <td>
                                <?php if ($sy['status'] === 'active'): ?>
                                    <span class="badge bg-label-success">Active</span>

                                <?php elseif ($sy['status'] === 'inactive'): ?>
                                    <span class="badge bg-label-danger">Inactive</span>

                                <?php elseif ($sy['status'] === 'archived'): ?>
                                    <span class="badge bg-label-secondary">Archived</span>

                                <?php endif; ?>
                            </td>
                            <td>
                                <button 
                                  class="btn btn-warning btn-sm" 
                                  data-bs-toggle="modal" 
                                  data-bs-target="#editSyModal"
                                  onclick="editSy(
                                    '<?= htmlspecialchars($sy['id']); ?>', 
                                    '<?= htmlspecialchars($sy['school_year']); ?>', 
                                    '<?= htmlspecialchars($sy['start_date']); ?>', 
                                    '<?= htmlspecialchars($sy['end_date']); ?>', 
                                    '<?= htmlspecialchars($sy['status']); ?>'
                                  )"
                                >
                                  Edit
                                </button>

                                <form action="../../../app/controllers/registrar/SchoolYearController.php" method="POST" class="d-inline">
                                    <?php echo Csrf::field(); ?>
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php echo htmlspecialchars($sy['id']); ?>">

                                    <button
                                        type="submit"
                                        name="delete_sy"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this school year? This action cannot be undone.')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            No school years found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>

      <?php if ($total_pages > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>Page <?php echo $current_page; ?> of <?php echo $total_pages; ?> (<?php echo $total_records; ?> total)</span>
            <nav>
                <ul class="pagination mb-0">
                    <?php
                        $qs = function ($p) use ($search_term, $status_filter) {
                            return '?' . http_build_query(['search' => $search_term, 'status' => $status_filter, 'page' => $p]);
                        };
                    ?>
                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $qs(max($current_page - 1, 1)); ?>">Previous</a>
                    </li>
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <li class="page-item <?php echo $p === $current_page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $qs($p); ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $qs(min($current_page + 1, $total_pages)); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
      <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    
    <!-- ── Vendor scripts ── -->
    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>
    <script src="../../../public/js/registrar/sy.js"></script>
</body>
</html>