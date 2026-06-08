<?php
session_start();

require_once __DIR__ . '/../../../app/middleware/Auth.php';
require_once __DIR__ . '/../../../app/helpers/message.php';
require_once __DIR__ . '/../../../app/models/UpdateProfileModel.php';
require_once __DIR__ . '/../../../database/config/config.php';

AuthRole::allowOnly(['registrar']); 

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
    <title> Dashboard | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <!-- Main content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Account Settings /</span> Account</h4>

        <!-- Display flash messages -->
        <?php showFlash(); ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <h5 class="card-header">Profile Details</h5>
                    <!-- Account -->
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
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
                            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="user-avatar" 
                                 class="d-block rounded" height="100" width="100" id="uploadedAvatar" style="object-fit: cover;">
                            <div class="button-wrapper">
                                <label for="profilePic" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">Upload new photo</span>
                                    <i class="bx bx-cloud-upload d-block d-sm-none"></i>
                                    <input type="file" id="profilePic" class="account-file-input" hidden 
                                           accept="image/png,image/jpeg" name="profile_pic" onchange="previewImage(event)">
                                </label>
                                <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
                                    <span class="d-none d-sm-block">Reset</span>
                                    <i class="bx bx-refresh d-block d-sm-none"></i>
                                </button>
                                <div class="text-muted small">JPG, PNG or GIF. Max size 2MB</div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-0">
                    <div class="card-body">
                        <form action="../../../app/controllers/UpdateProfile.php" method="POST" enctype="multipart/form-data" id="formAccountSettings">
                            <!-- Basic Information Section -->
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Basic Information</h6>
                                <div class="row">
                                    <!-- Full Name -->
                                    <div class="mb-3 col-md-6">
                                        <label for="fullName" class="form-label">Full Name</label>
                                        <input class="form-control" type="text" id="fullName" name="full_name" 
                                               value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" required>
                                    </div>

                                    <!-- Email Address -->
                                    <div class="mb-3 col-md-6">
                                        <label for="email" class="form-label">E-mail</label>
                                        <input class="form-control" type="email" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                                    </div>

                                    <!-- Account Role -->
                                    <div class="mb-3 col-md-6">
                                        <label for="role" class="form-label">Role</label>
                                        <input class="form-control" type="text" id="role" 
                                               value="<?php echo ucfirst(htmlspecialchars($_SESSION['role'] ?? 'N/A')); ?>" disabled>
                                    </div>

                                    <!-- Member Since -->
                                    <div class="mb-3 col-md-6">
                                        <label for="memberSince" class="form-label">Member Since</label>
                                        <input class="form-control" type="text" id="memberSince" 
                                               value="<?php echo htmlspecialchars($memberSince); ?>" disabled>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Security Settings Section -->
                            <div class="mb-4">
                                <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Security Settings</h6>
                                
                                <!-- Current Password -->
                                <div class="mb-3">
                                    <label class="form-label" for="currentPassword">
                                        Current Password
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="currentPassword" name="current_password" 
                                           placeholder="Enter your current password to confirm changes" required>
                                    <small class="text-muted d-block mt-1">Required to confirm any changes</small>
                                </div>

                                <!-- New Password -->
                                <div class="mb-3">
                                    <label class="form-label" for="newPassword">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" name="new_password" 
                                           placeholder="Leave empty to keep current password">
                                    <small class="text-muted d-block mt-1">Minimum 8 characters</small>
                                </div>

                                <!-- Confirm New Password -->
                                <div class="mb-3">
                                    <label class="form-label" for="confirmPassword">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" 
                                           placeholder="Re-enter new password">
                                </div>
                            </div>

                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary me-2">Save changes</button>
                                <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>
                    <!-- /Account -->
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    
    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <!-- Profile Picture Preview Script -->
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const avatar = document.getElementById('uploadedAvatar');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatar.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Reset image button functionality
        document.querySelector('.account-image-reset').addEventListener('click', function() {
            const fileInput = document.getElementById('profilePic');
            fileInput.value = '';
            document.getElementById('uploadedAvatar').src = '<?php echo htmlspecialchars($profilePic); ?>';
        });
    </script>
</body>
</html>