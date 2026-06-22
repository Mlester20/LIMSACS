<?php
require_once __DIR__ . '/../../../app/controllers/admin/UsersController.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
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
    <title> Users | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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

    <!-- Search / Filter + Add User -->
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <input
                type="text"
                name="search"
                class="form-control"
                style="max-width: 240px;"
                placeholder="Search name or email"
                value="<?php echo htmlspecialchars($search_term); ?>">

            <select name="role" class="form-select" style="max-width: 160px;">
                <option value="">All Roles</option>
                <?php foreach (['admin', 'registrar', 'teacher', 'staff'] as $roleOption): ?>
                    <option value="<?php echo $roleOption; ?>" <?php echo $role_filter === $roleOption ? 'selected' : ''; ?>>
                        <?php echo ucfirst($roleOption); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-outline-secondary">Filter</button>
            <?php if ($search_term !== '' || $role_filter !== ''): ?>
                <a href="users.php" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addUserModal">
            Add User
        </button>
    </div>

    <!-- Add User Modal -->
    <div
        class="modal fade"
        id="addUserModal"
        tabindex="-1"
        aria-labelledby="addUserModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-lg">
            <form
                action="../../../app/controllers/admin/UsersController.php"
                method="POST"
                enctype="multipart/form-data">
                <?php echo Csrf::field(); ?>

                <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header text-white">
                        <h5 class="modal-title" id="addUserModalLabel">
                            Add User
                        </h5>

                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">

                        <div class="row">

                            <!-- Full Name -->
                            <div class="col-md-12 mb-3">
                                <label for="full_name" class="form-label">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="full_name"
                                    name="full_name"
                                    placeholder="Enter full name"
                                    required>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="example@email.com"
                                    required>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    Password
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="Enter password"
                                    required>
                            </div>

                            <!-- Role -->
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">
                                    Role
                                </label>

                                <select
                                    class="form-select"
                                    id="role"
                                    name="role"
                                    required>

                                    <option value="" selected disabled>
                                        Select Role
                                    </option>

                                    <option value="admin">
                                        Admin
                                    </option>

                                    <option value="registrar">
                                        Registrar
                                    </option>

                                    <option value="teacher">
                                        Teacher
                                    </option>

                                    <option value="staff">
                                        Staff
                                    </option>

                                </select>
                            </div>

                            <!-- Profile Picture -->
                            <div class="col-md-6 mb-3">
                                <label for="profile_picture" class="form-label">
                                    Profile Picture
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    id="profile_picture"
                                    name="profile_picture"
                                    accept="image/*">
                            </div>

                            <!-- Preview -->
                            <div class="col-md-12 text-center mt-2">
                                <img
                                    id="imagePreview"
                                    src="https://placehold.co/120x120"
                                    alt="Preview"
                                    class="rounded-circle border"
                                    style="width:120px;height:120px;object-fit:cover;">
                            </div>

                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button
                            type="submit"
                            name="create_user"
                            class="btn btn-primary">
                            Save User
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div
        class="modal fade"
        id="editUserModal"
        tabindex="-1"
        aria-labelledby="editUserModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-lg">
            <form
                action="../../../app/controllers/admin/UsersController.php"
                method="POST"
                enctype="multipart/form-data">
                <?php echo Csrf::field(); ?>

                <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header text-white">
                        <h5 class="modal-title" id="editUserModalLabel">
                            Edit User
                        </h5>

                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">

                        <div class="row">

                            <!-- User ID (Hidden) -->
                            <input
                                type="hidden"
                                id="edit_user_id"
                                name="id"
                                value="">

                            <!-- Full Name -->
                            <div class="col-md-12 mb-3">
                                <label for="edit_full_name" class="form-label">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="edit_full_name"
                                    name="full_name"
                                    placeholder="Enter full name"
                                    required>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="edit_email" class="form-label">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="edit_email"
                                    name="email"
                                    placeholder="example@email.com"
                                    required>
                            </div>

                            <!-- Role -->
                            <div class="col-md-6 mb-3">
                                <label for="edit_role" class="form-label">
                                    Role
                                </label>

                                <select
                                    class="form-select"
                                    id="edit_role"
                                    name="role"
                                    required>

                                    <option value="" selected disabled>
                                        Select Role
                                    </option>

                                    <option value="admin">
                                        Admin
                                    </option>

                                    <option value="registrar">
                                        Registrar
                                    </option>

                                    <option value="teacher">
                                        Teacher
                                    </option>

                                    <option value="staff">
                                        Staff
                                    </option>

                                </select>
                            </div>

                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button
                            type="submit"
                            name="update_user"
                            class="btn btn-primary">
                            Update User
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div
        class="modal fade"
        id="resetPasswordModal"
        tabindex="-1"
        aria-labelledby="resetPasswordModalLabel"
        aria-hidden="true">

        <div class="modal-dialog">
            <form
                action="../../../app/controllers/admin/UsersController.php"
                method="POST"
                id="resetPasswordForm">
                <?php echo Csrf::field(); ?>

                <div class="modal-content">

                    <!-- Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="resetPasswordModalLabel">
                            Reset Password
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body">
                        <input type="hidden" id="reset_user_id" name="id" value="">

                        <p class="mb-3">
                            Set a new password for <strong id="reset_user_name"></strong>.
                            They will need to use this new password the next time they log in.
                        </p>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input
                                type="password"
                                class="form-control"
                                id="new_password"
                                name="new_password"
                                placeholder="Enter new password"
                                minlength="8"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input
                                type="password"
                                class="form-control"
                                id="confirm_password"
                                placeholder="Re-enter new password"
                                minlength="8"
                                required>
                            <div class="invalid-feedback" id="confirm_password_feedback">Passwords do not match.</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button
                            type="submit"
                            name="reset_password"
                            class="btn btn-primary">
                            Reset Password
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <h5 class="card-header">Users</h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created Since</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button 
                                        class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserModal"
                                        onclick="updateUser(<?php echo htmlspecialchars($user['id']); ?>, '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['role']); ?>')">
                                        Edit
                                    </button>

                                    <button
                                        class="btn btn-sm btn-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#resetPasswordModal"
                                        title="Reset this user's password"
                                        onclick="resetPasswordModal(<?php echo htmlspecialchars($user['id']); ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')">
                                        Reset Password
                                    </button>

                                    <?php if ((int)$user['id'] === (int)($_SESSION['id'] ?? 0)): ?>
                                        <button class="btn btn-sm btn-danger" disabled title="You cannot delete your own account">Delete</button>
                                    <?php else: ?>
                                        <form action="../../../app/controllers/admin/UsersController.php" method="post" style="display: inline;">
                                            <?php echo Csrf::field(); ?>
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>"/>
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this user? ')"
                                                name="delete_user"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach;?>
                    <?php else:?>
                        <tr>
                            <td colspan="5" class="text-center">
                                No users found
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
                            $qs = function ($p) use ($search_term, $role_filter) {
                                return '?' . http_build_query(['search' => $search_term, 'role' => $role_filter, 'page' => $p]);
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
    <script src="../../../public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>

    <!-- ── Chart.js ── -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script src="../../../public/js/admin/dashboard.js"></script>
    <script src="../../../public/js/admin/users.js"></script>
</body>
</html>