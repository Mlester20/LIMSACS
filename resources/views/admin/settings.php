<?php
session_start();

require_once __DIR__ . '/../../../app/middleware/Auth.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
require_once __DIR__ . '/../../../app/models/UpdateProfileModel.php';
require_once __DIR__ . '/../../../database/config/config.php';

AuthRole::allowOnly(['admin']); 

// Get user profile data
$updateProfileModel = new UpdateProfileModel($con);
$userProfile = $updateProfileModel->getUserById($_SESSION['id']);

// Format the Member Since date
$memberSince = $userProfile['created_at'] ? date('M d, Y', strtotime($userProfile['created_at'])) : 'Unknown';
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
    <title> Profile | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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
    
    <!-- Display flash messages -->
    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <!-- Main content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4"><span class="text-muted fw-light">Account /</span> Profile Settings</h4>

        <div class="row">
            <!-- Profile Information Section -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title mb-3">Profile Information</h5>
                        
                        <!-- Profile Picture -->
                        <?php
                        $profilePic = $userProfile['profile_picture'] ?? null;
                        $defaultProfilePic = '../../../public/assets/img/avatars/1.png';
                        
                        if ($profilePic) {
                            // Resolve the profile picture path
                            $resolvedPath = __DIR__ . '/../../..' . '/' . $profilePic;
                            if (file_exists($resolvedPath)) {
                                $profilePic = '../../../' . $profilePic;
                            } else {
                                $profilePic = $defaultProfilePic;
                            }
                        } else {
                            $profilePic = $defaultProfilePic;
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" 
                             class="rounded-circle mb-3" width="100" height="100" style="object-fit: cover;">
                        
                        <!-- User Info -->
                        <div class="text-start mt-4">
                            <div class="mb-3">
                                <label class="d-block text-muted small mb-1">Full Name</label>
                                <p class="mb-0 fw-medium"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'N/A'); ?></p>
                            </div>

                            <div class="mb-3">
                                <label class="d-block text-muted small mb-1">Email Address</label>
                                <p class="mb-0 fw-medium"><?php echo htmlspecialchars($_SESSION['email'] ?? 'N/A'); ?></p>
                            </div>

                            <div class="mb-3">
                                <label class="d-block text-muted small mb-1">Account Role</label>
                                <div>
                                    <span class="badge bg-label-primary">
                                        <?php echo ucfirst(htmlspecialchars($_SESSION['role'] ?? 'N/A')); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="d-block text-muted small mb-1">Member Since</label>
                                <p class="mb-0 fw-medium"><?php echo htmlspecialchars($memberSince); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Profile Section -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Update Profile</h5>

                        <form action="../../../app/controllers/UpdateProfile.php" method="POST" enctype="multipart/form-data">
                            <!-- BASIC INFORMATION -->
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Basic Information</h6>

                                <!-- Profile Picture Upload -->
                                <div class="mb-3">
                                    <label for="profilePic" class="form-label">Profile Picture</label>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">
                                            Current: <strong><?php echo htmlspecialchars($userProfile['profile_picture'] ?? 'Default Avatar'); ?></strong>
                                        </small>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="profilePic" name="profile_pic" 
                                               accept=".jpg,.jpeg,.png,.gif" onchange="previewImage(event)">
                                        <label class="input-group-text">Choose File</label>
                                    </div>
                                    <small class="text-muted d-block mt-2">JPG, PNG, or GIF (Max 2MB)</small>
                                    <div id="previewContainer" class="mt-2"></div>
                                </div>

                                <!-- Full Name -->
                                <div class="mb-3">
                                    <label for="fullName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="fullName" name="full_name" 
                                           value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" required>
                                </div>

                                <!-- Email Address -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- SECURITY SETTINGS -->
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Security Settings</h6>

                                <!-- Current Password -->
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">
                                        Current Password
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="currentPassword" name="current_password" 
                                           placeholder="Enter your current password" required>
                                    <small class="text-muted d-block mt-1">Required to confirm changes</small>
                                </div>

                                <!-- New Password -->
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" name="new_password" 
                                           placeholder="Leave empty to keep current password">
                                    <small class="text-muted d-block mt-1">Minimum 8 characters</small>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" 
                                           placeholder="Re-enter new password">
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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

    <!-- ── Chart.js ── -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script src="../../../public/js/admin/dashboard.js"></script>

    <!-- Profile Picture Preview Script -->
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const previewContainer = document.getElementById('previewContainer');
            previewContainer.innerHTML = '';

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '150px';
                    img.style.maxHeight = '150px';
                    img.classList.add('rounded');
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>