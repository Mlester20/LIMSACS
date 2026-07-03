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
        <li class="menu-item <?php echo ($currentPage === 'academic-history.php') ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-user"></i>
            <div data-i18n="Account Settings">Enrolled Students</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="<?= BASE_URL ?>/resources/views/admin/academic-history.php" class="menu-link"><div data-i18n="Account">Academic History</div></a></li>
          </ul>
        </li>
        <li class="menu-item <?php echo (in_array($currentPage, ['graduates.php', 'graduates-master-list.php', 'graduate-view.php'])) ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-medal"></i>
            <div data-i18n="Graduates">Graduates</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="<?= BASE_URL ?>/resources/views/admin/graduates.php" class="menu-link"><div data-i18n="Dashboard">Dashboard</div></a></li>
            <li class="menu-item"><a href="<?= BASE_URL ?>/resources/views/admin/graduates-master-list.php" class="menu-link"><div data-i18n="Master List">Master List</div></a></li>
          </ul>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'school-year.php') ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-book-open"></i>
            <div data-i18n="School Year">School Year</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="<?= BASE_URL ?>/resources/views/admin/school-year.php" class="menu-link"><div data-i18n="Basic">School Year</div></a></li>
          </ul>
        </li>
        <li class="menu-item <?php echo ($currentPage === 'users.php') ? 'active' : ''; ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-user"></i>
            <div data-i18n="Misc">Users</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item"><a href="<?= BASE_URL ?>/resources/views/admin/users.php" class="menu-link"><div data-i18n="Users">Users</div></a></li>
          </ul>
        </li>

          <!-- Audits -->
          <li class="menu-header small text-uppercase"><span class="menu-header-text">Audits</span></li>
            <li class="menu-item <?php echo ($currentPage === 'audit-logs.php') ? 'active' : '' ?> ">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Misc">Audit Logs</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item"><a href="<?= BASE_URL ?>/resources/views/admin/audit-logs.php" class="menu-link"><div data-i18n="Activity Logs">Activity Logs</div></a></li>
              </ul>
          </li>
      </ul>
    </aside>
    <!-- / Menu -->

    <!-- Layout page -->
    <div class="layout-page">
