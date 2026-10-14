<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">

    <!-- Menu -->
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
        <a href="<?= BASE_URL ?>/resources/views/admin/dashboard.php" class="app-brand-link">
          <span class="app-brand-logo demo">
            <img src="<?= BASE_URL ?>/public/assets/img/favicon/logo.png" alt="Logo" style="width: 50x; height: 50px;">
          </span>
          <span class="app-brand-text demo menu-text fw-bolder ms-2">LIMSACS</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
          <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
      </div>

      <div class="menu-inner-shadow"></div>

      <ul class="menu-inner py-1">
        <li class="menu-item <?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/admin/dashboard.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div data-i18n="Analytics">Dashboard</div>
          </a>
        </li>
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Pages</span>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'student-records.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/admin/student-records.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-id-card"></i>
            <div data-i18n="Student Master List">Student Master List</div>
          </a>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'academic-history.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/admin/academic-history.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-user"></i>
            <div data-i18n="Academic History">Academic History</div>
          </a>
        </li>
        <li class="menu-item <?php echo (in_array($currentPage, ['graduates.php', 'graduates-master-list.php', 'graduate-view.php'])) ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/admin/graduates.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-medal"></i>
            <div data-i18n="Graduates">Graduates</div>
          </a>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'school-year.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/admin/school-year.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-book-open"></i>
            <div data-i18n="School Year">School Year</div>
          </a>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'student-documents.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/admin/student-documents.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-file-blank"></i>
            <div data-i18n="Student Documents">Student Documents</div>
          </a>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'parent-guardians.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/admin/parent-guardians.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-group"></i>
            <div data-i18n="Parents & Guardians">Parents & Guardians</div>
          </a>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'sections.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/admin/sections.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-collection"></i>
            <div data-i18n="Sections">Sections</div>
          </a>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'users.php') ? 'active' : ''; ?>">
          <a href="<?= BASE_URL ?>/resources/views/admin/users.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-user"></i>
            <div data-i18n="Users">Users</div>
          </a>
        </li>

          <!-- Audits -->
          <li class="menu-header small text-uppercase"><span class="menu-header-text">Audits</span></li>
            <li class="menu-item <?php echo ($currentPage === 'audit-logs.php') ? 'active' : '' ?> ">
              <a href="<?= BASE_URL ?>/resources/views/admin/audit-logs.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Activity Logs">Logs</div>
              </a>
          </li>

          <!-- System -->
          <li class="menu-header small text-uppercase"><span class="menu-header-text">System</span></li>
            <li class="menu-item <?php echo ($currentPage === 'backup.php') ? 'active' : '' ?> ">
              <a href="<?= BASE_URL ?>/resources/views/admin/backup.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cloud-download"></i>
                <div data-i18n="Backup">Backup</div>
              </a>
          </li>
      </ul>
    </aside>
    <!-- / Menu -->

    <!-- Layout page -->
    <div class="layout-page">
