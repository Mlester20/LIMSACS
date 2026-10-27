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
              <i class="bx bx-file fs-4 lh-0 me-2 text-muted"></i>
              <span class="text-muted small fw-semibold">LEARNER'S INFORMATION MANAGEMENT SYSTEM OF AURORA CENTRAL SCHOOL</span>
            </div>
          </div>
          <ul class="navbar-nav flex-row align-items-center ms-auto">
            <?php
              require_once __DIR__ . '/../../../../app/models/NotificationsModel.php';
              require_once __DIR__ . '/../../../../app/helpers/timeAgo.php';
              require_once __DIR__ . '/../../../../app/helpers/csrf.php';
              $notificationsModel = new NotificationsModel($con);
              $recentNotifications = $notificationsModel->getRecentForUser($_SESSION['id'] ?? 0, 10);
              $unreadNotificationCount = $notificationsModel->countUnread($_SESSION['id'] ?? 0);
            ?>
            <li class="nav-item dropdown" id="notificationBell" data-csrf="<?php echo htmlspecialchars(Csrf::token()); ?>">
              <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class="bx bx-bell bx-sm"></i>
                <span
                  id="notificationBadge"
                  class="badge bg-danger rounded-pill position-absolute top-0 end-0 <?php echo $unreadNotificationCount > 0 ? '' : 'd-none'; ?>"
                  style="font-size: 0.65rem; transform: translate(10%, -10%);"
                ><?php echo $unreadNotificationCount; ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" style="width: 340px; max-height: 420px; overflow-y: auto;">
                <li class="dropdown-header d-flex justify-content-between align-items-center">
                  <h6 class="mb-0">Notifications</h6>
                </li>
                <li><div class="dropdown-divider"></div></li>
                <?php if(empty($recentNotifications)): ?>
                  <li class="text-center text-muted py-4 small">
                    <i class="bx bx-bell-off fs-3 d-block mb-1"></i>
                    No notifications yet.
                  </li>
                <?php else: ?>
                  <?php foreach($recentNotifications as $notif): ?>
                    <li>
                      <a class="dropdown-item <?php echo !$notif['is_read'] ? 'bg-light' : ''; ?>" href="<?php echo htmlspecialchars($notif['link'] ?: '#'); ?>">
                        <div class="d-flex align-items-start">
                          <i class="bx bx-file-blank text-primary me-2 mt-1"></i>
                          <div class="flex-grow-1">
                            <span class="fw-semibold small d-block"><?php echo htmlspecialchars($notif['title']); ?></span>
                            <span class="text-muted small d-block text-wrap"><?php echo htmlspecialchars($notif['message']); ?></span>
                            <span class="text-muted" style="font-size: 0.7rem;"><?php echo timeAgo($notif['created_at']); ?></span>
                          </div>
                        </div>
                      </a>
                    </li>
                  <?php endforeach; ?>
                <?php endif; ?>
              </ul>
            </li>
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
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/resources/views/registrar/settings.php"><i class="bx bx-user me-2"></i><span class="align-middle">My Profile</span></a></li>
                <li><div class="dropdown-divider"></div></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/app/controllers/Logout.php" onclick="return confirm('Are you sure you to logout?')"><i class="bx bx-power-off me-2"></i><span class="align-middle">Log Out</span></a></li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
      <!-- / Navbar -->

      <script>
        document.addEventListener('DOMContentLoaded', function () {
          var bell = document.getElementById('notificationBell');
          var badge = document.getElementById('notificationBadge');
          if (!bell || !badge) return;

          bell.addEventListener('shown.bs.dropdown', function () {
            if (badge.classList.contains('d-none')) return;
            badge.classList.add('d-none');

            fetch('<?= BASE_URL ?>/app/controllers/NotificationsController.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'mark_all_read=1&csrf_token=' + encodeURIComponent(bell.dataset.csrf),
              credentials: 'same-origin'
            }).catch(function () {});
          });
        });
      </script>

      <!-- Content wrapper -->
      <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
