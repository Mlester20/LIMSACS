<!-- Navbar -->
      <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
          <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
          </a>
        </div>
        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
          <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
              <i class="bx bx-search fs-4 lh-0"></i>
              <input type="text" class="form-control border-0 shadow-none" placeholder="Search..." aria-label="Search..." />
            </div>
          </div>
          <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
              <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <?php
                      $profile_pic = !empty($_SESSION['profile_picture'])
                          ? htmlspecialchars($_SESSION['profile_picture'])
                          : 'public/assets/img/avatars/1.png';
                    ?>
                    <img
                        src="<?php echo BASE_URL . '/' . ltrim($profile_pic, '/'); ?>"
                        alt="Profile Picture"
                        class="rounded-circle mb-3"
                    />
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="#">
                    <div class="d-flex">
                      <div class="flex-shrink-0 me-3">
                        <div class="avatar avatar-online">
                            <?php
                              $profile_pic = !empty($_SESSION['profile_picture'])
                                  ? htmlspecialchars($_SESSION['profile_picture'])
                                  : 'public/assets/img/avatars/1.png';
                            ?>
                            <img
                                src="<?php echo BASE_URL . '/' . ltrim($profile_pic, '/'); ?>"
                                alt="Profile Picture"
                                class="rounded-circle mb-3"
                            />
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <span class="fw-semibold d-block"> <?php echo htmlspecialchars($_SESSION['full_name']); ?> </span>
                        <small class="text-muted"><?php echo htmlspecialchars($_SESSION['email']); ?></small>
                      </div>
                    </div>
                  </a>
                </li>
                <li><div class="dropdown-divider"></div></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/resources/views/teachers/settings.php"><i class="bx bx-user me-2"></i><span class="align-middle">My Profile</span></a></li>
                <li><div class="dropdown-divider"></div></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/controllers/Logout.php" onclick="return confirm('Are you sure you to logout?')"><i class="bx bx-power-off me-2"></i><span class="align-middle">Log Out</span></a></li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
      <!-- / Navbar -->

      <!-- Content wrapper -->
      <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
